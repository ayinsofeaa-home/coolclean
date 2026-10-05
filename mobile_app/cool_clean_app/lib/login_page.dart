import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'register_page.dart';
import 'customer/home_page.dart' as customer;
import 'driver/homepage.dart' as driver;

/// SHARED login page - used by BOTH customers and drivers.
/// After a successful login, we read `role` from the API response and
/// navigate to the correct home page (customer or driver).
class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController emailController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  bool isLoggingIn = false;
  bool obscurePassword = true;

  // TODO: change this to your actual server IP/domain when deploying.
  final String apiUrl = 'http://192.168.0.88:8000/api/auth/login';

  @override
  void dispose() {
    emailController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  Future<void> login() async {
    final email = emailController.text.trim();
    final password = passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      showMessage('Please enter your email and password.');
      return;
    }

    setState(() => isLoggingIn = true);

    try {
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'email': email, 'password': password}),
      );

      debugPrint('STATUS: ${response.statusCode}');
      debugPrint('BODY: ${response.body}');

      final data = jsonDecode(response.body);

      if (!mounted) return;

      if (response.statusCode == 200) {
        // MobileAuthController@login returns the user object directly
        // (no wrapper), with fields: id, fullName, email, phone, role,
        // status, etc. role is uppercase: "CUSTOMER" or "DRIVER".
        final role = data['role'];

        if (role == 'CUSTOMER') {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => customer.HomePage(
                userName: data['fullName'] ?? email.split('@')[0],
              ),
            ),
          );
        } else if (role == 'DRIVER') {
          // Newly registered drivers start as PENDING_APPROVAL but are
          // still allowed to log in (backend doesn't block that status).
          if (data['status'] == 'PENDING_APPROVAL') {
            showMessage('Your driver account is still pending admin approval.');
          }
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const driver.Homepage()),
          );
        } else {
          // ADMIN/SUPER_ADMIN are blocked by the API with a 403 already,
          // so we should rarely land here - but handle it just in case.
          showMessage('This account type is not supported in the app.');
        }
      } else {
        showMessage(data['message'] ?? 'Login failed.');
      }
    } catch (e) {
      debugPrint('LOGIN ERROR: $e');
      if (!mounted) return;
      showMessage('Unable to connect to the server.');
    } finally {
      if (mounted) setState(() => isLoggingIn = false);
    }
  }

  void showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 40),

              Center(
                child: Image.asset(
                  'Assets/logo/CoolCleanLogo.png',
                  width: 140,
                  height: 140,
                ),
              ),
              const SizedBox(height: 20),

              Text(
                'Welcome to CoolClean',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: Colors.lightBlue[900],
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'Sign in as a customer or driver',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: Colors.grey[600]),
              ),
              const SizedBox(height: 32),

              Text('Email', style: TextStyle(fontSize: 13, color: Colors.grey[700])),
              const SizedBox(height: 6),
              TextField(
                controller: emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  hintText: 'unknown@example.com',
                  prefixIcon: const Icon(Icons.email_outlined, color: Colors.grey),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              Text('Password', style: TextStyle(fontSize: 13, color: Colors.grey[700])),
              const SizedBox(height: 6),
              TextField(
                controller: passwordController,
                obscureText: obscurePassword,
                decoration: InputDecoration(
                  hintText: '••••••••',
                  prefixIcon: const Icon(Icons.lock_outline, color: Colors.grey),
                  suffixIcon: IconButton(
                    icon: Icon(
                      obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                      color: Colors.grey,
                    ),
                    onPressed: () => setState(() => obscurePassword = !obscurePassword),
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(color: Colors.grey.shade300),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: const BorderSide(color: Colors.teal, width: 2),
                  ),
                ),
              ),
              const SizedBox(height: 28),

              SizedBox(
                height: 50,
                child: ElevatedButton(
                  onPressed: isLoggingIn ? null : login,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.teal,
                    disabledBackgroundColor: Colors.grey.shade300,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: Text(
                    isLoggingIn ? 'Logging in...' : 'Login',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: isLoggingIn ? Colors.grey[600] : Colors.white,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),

              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (context) => const RegisterPage()),
                        );
                      },
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        side: BorderSide(color: Colors.grey.shade300),
                      ),
                      child: Text(
                        'Register',
                        style: TextStyle(color: Colors.lightBlue[900], fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextButton(
                      onPressed: () {
                        // TODO: navigate to Forgot Password page
                      },
                      child: Text('Forgot password?', style: TextStyle(color: Colors.grey[700])),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }
}
