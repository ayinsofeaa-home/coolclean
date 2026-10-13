import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../login_page.dart';

class DriverRegister extends StatefulWidget {
  const DriverRegister({super.key});

  @override
  State<DriverRegister> createState() => _DriverRegisterState();
}

class _DriverRegisterState extends State<DriverRegister> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController fullNameController = TextEditingController();
  final TextEditingController emailController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();
  final TextEditingController addressController = TextEditingController();
  final TextEditingController postcodeController = TextEditingController();
  final TextEditingController cityController = TextEditingController();
  final TextEditingController plateController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final TextEditingController confirmPasswordController = TextEditingController();

  String? selectedServiceState;
  String? selectedVehicleType;
  bool obscurePassword = true;
  bool obscureConfirmPassword = true;

  // Address validation state
  bool isValidatingAddress = false;
  bool isAddressValidated = false;
  double? validatedLatitude;
  double? validatedLongitude;
  String? validatedDisplayAddress;

  // TODO: keep this in sync with the IP/port you're using in login_page.dart.
  static const String baseUrl = 'http://192.168.0.100:8000/api';

  bool isSubmitting = false;

  /// Call this whenever address/postcode/city/state changes, so a stale
  /// pinned location can't be submitted alongside an edited address.
  void _resetAddressValidation() {
    if (isAddressValidated) {
      setState(() {
        isAddressValidated = false;
        validatedLatitude = null;
        validatedLongitude = null;
        validatedDisplayAddress = null;
      });
    }
  }

  // ADDRESS VALIDATION DISABLED (kept for later):
  // The address validation feature is commented out for now, so any address
  // the user enters is accepted immediately.
  /*
  /// Calls OpenStreetMap's free Nominatim geocoding API to check whether
  /// the typed address is a real, findable location, and stores the
  /// resulting coordinates if so. No API key, account, or billing needed.
  Future<void> _validateAddress() async {
    final address = addressController.text.trim();
    final postcode = postcodeController.text.trim();
    final city = cityController.text.trim();
    final state = selectedServiceState ?? '';

    if (address.isEmpty || city.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in address and city before validating.')),
      );
      return;
    }

    setState(() {
      isValidatingAddress = true;
      isAddressValidated = false;
    });

    // Combine the fields into one query string, e.g.:
    // "12 Jalan Bunga, 43000, Kajang, Selangor, Malaysia"
    final query = [address, postcode, city, state, 'Malaysia']
        .where((part) => part.isNotEmpty)
        .join(', ');

    final uri = Uri.https('nominatim.openstreetmap.org', '/search', {
      'q': query,
      'format': 'json',
      'limit': '1',
    });

    try {
      final response = await http.get(
        uri,
        // Nominatim's usage policy requires a descriptive User-Agent.
        headers: {'User-Agent': 'CoolCleanApp/1.0 (student FYP project)'},
      );

      if (response.statusCode == 200) {
        final results = jsonDecode(response.body) as List;
        if (results.isNotEmpty) {
          final result = results.first as Map<String, dynamic>;
          setState(() {
            validatedLatitude = double.parse(result['lat']);
            validatedLongitude = double.parse(result['lon']);
            validatedDisplayAddress = result['display_name'];
            isAddressValidated = true;
          });
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text('Address found: $validatedDisplayAddress')),
            );
          }
        } else {
          setState(() => isAddressValidated = false);
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Address not found. Please check and try again.'),
              ),
            );
          }
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Could not reach the address lookup service.')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Network error while validating address.')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => isValidatingAddress = false);
      }
    }
  }
  */

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    final email = emailController.text.trim().toLowerCase();

    setState(() => isSubmitting = true);

    try {
      // Step 1: request an OTP for this email.
      final otpResponse = await http.post(
        Uri.parse('$baseUrl/auth/otp'),
        headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: jsonEncode({'email': email}),
      );

      if (!mounted) return;

      if (otpResponse.statusCode != 200) {
        final body = jsonDecode(otpResponse.body);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(body['message'] ?? 'Could not send OTP.')),
        );
        setState(() => isSubmitting = false);
        return;
      }

      // Step 2: ask the user to enter the OTP they received by email.
      final otp = await _askForOtp();
      if (otp == null || otp.isEmpty) {
        setState(() => isSubmitting = false);
        return; // user cancelled
      }

      // Step 3: submit the full registration with the OTP.
      final registerResponse = await http.post(
        Uri.parse('$baseUrl/auth/register/driver'),
        headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
        body: jsonEncode({
          'fullName': fullNameController.text.trim(),
          'email': email,
          'phone': phoneController.text.trim(),
          'otp': otp,
          'password': passwordController.text,
          'confirmPassword': confirmPasswordController.text,
          'vehicleType': selectedVehicleType,
          'vehiclePlate': plateController.text.trim(),
          'serviceAddress': addressController.text.trim(),
          'servicePostcode': postcodeController.text.trim(),
          'serviceCity': cityController.text.trim(),
          'serviceState': selectedServiceState,
          'serviceLatitude': validatedLatitude,
          'serviceLongitude': validatedLongitude,
        }),
      );

      if (!mounted) return;

      final data = jsonDecode(registerResponse.body);

      if (registerResponse.statusCode == 200) {
        // Registration succeeded - account is created with
        // STATUS = 'PENDING_APPROVAL' on the backend.
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Registration submitted'),
            content: const Text(
              'Your driver account has been created and is pending admin approval. '
                  "You'll be notified once it's reviewed.",
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (context) => const LoginPage()),
                        (route) => false,
                  );
                },
                child: const Text('Back to login'),
              ),
            ],
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(data['message'] ?? 'Registration failed.')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Unable to connect to the server.')),
        );
      }
    } finally {
      if (mounted) setState(() => isSubmitting = false);
    }
  }

  /// Shows a simple dialog asking the user to type in the OTP they were
  /// emailed. Returns the entered code, or null if they cancelled.
  Future<String?> _askForOtp() async {
    final otpController = TextEditingController();
    return showDialog<String>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Enter OTP'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('We sent a 6-digit code to your email. It expires in 5 minutes.'),
            const SizedBox(height: 12),
            TextField(
              controller: otpController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              decoration: const InputDecoration(hintText: '123456'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, null),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, otpController.text.trim()),
            child: const Text('Confirm'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back, color: Colors.lightBlue[900]),
          onPressed: () {
            Navigator.pop(context);
          },
        ),
        title: Text(
          'Driver Register',
          style: TextStyle(
            color: Colors.lightBlue[900],
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Full name
              TextFormField(
                controller: fullNameController,
                decoration: _fieldDecoration('Full name', Icons.person_outline),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your name' : null,
              ),
              const SizedBox(height: 14),

              // Email
              TextFormField(
                controller: emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: _fieldDecoration('Email', Icons.email_outlined),
                validator: (value) {
                  if (value == null || value.isEmpty) return 'Please enter your email';
                  if (!value.contains('@')) return 'Please enter a valid email';
                  return null;
                },
              ),
              const SizedBox(height: 14),

              // Phone
              TextFormField(
                controller: phoneController,
                keyboardType: TextInputType.phone,
                decoration: _fieldDecoration('Phone', Icons.phone_outlined),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your phone number' : null,
              ),
              const SizedBox(height: 14),

              // Service address
              TextFormField(
                controller: addressController,
                decoration: _fieldDecoration('Service address', Icons.location_on_outlined),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your service address' : null,
                onChanged: (_) => _resetAddressValidation(),
              ),
              const SizedBox(height: 14),

              // Service postcode
              TextFormField(
                controller: postcodeController,
                keyboardType: TextInputType.number,
                decoration: _fieldDecoration('Service postcode', Icons.local_post_office_outlined),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your postcode' : null,
                onChanged: (_) => _resetAddressValidation(),
              ),
              const SizedBox(height: 14),

              // Service city
              TextFormField(
                controller: cityController,
                decoration: _fieldDecoration('Service city', Icons.location_city_outlined),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your city' : null,
                onChanged: (_) => _resetAddressValidation(),
              ),
              const SizedBox(height: 14),

              // Service state (dropdown)
              DropdownButtonFormField<String>(
                initialValue: selectedServiceState,
                decoration: _fieldDecoration('Service state', Icons.map_outlined),
                items: const [
                  DropdownMenuItem(value: 'Selangor', child: Text('Selangor')),
                  DropdownMenuItem(value: 'Kuala Lumpur', child: Text('Kuala Lumpur')),
                  DropdownMenuItem(value: 'Johor', child: Text('Johor')),
                  DropdownMenuItem(value: 'Penang', child: Text('Penang')),
                ],
                validator: (value) => value == null ? 'Please select a state' : null,
                onChanged: (value) {
                  setState(() {
                    selectedServiceState = value;
                  });
                  _resetAddressValidation();
                },
              ),
              const SizedBox(height: 14),

              // Vehicle type (dropdown)
              DropdownButtonFormField<String>(
                initialValue: selectedVehicleType,
                decoration: _fieldDecoration('Vehicle type', Icons.two_wheeler_outlined),
                items: const [
                  DropdownMenuItem(value: 'Motorcycle', child: Text('Motorcycle')),
                  DropdownMenuItem(value: 'Car', child: Text('Car')),
                  DropdownMenuItem(value: 'Van', child: Text('Van')),
                ],
                validator: (value) => value == null ? 'Please select a vehicle type' : null,
                onChanged: (value) {
                  setState(() {
                    selectedVehicleType = value;
                  });
                },
              ),
              const SizedBox(height: 14),

              // Vehicle plate number
              TextFormField(
                controller: plateController,
                decoration: _fieldDecoration('Vehicle plate number', Icons.confirmation_number_outlined),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your plate number' : null,
              ),
              const SizedBox(height: 24),

              // Section title
              Text(
                'Driver verification documents',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: Colors.lightBlue[900],
                ),
              ),
              const SizedBox(height: 16),

              // Driver license upload
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Colors.teal.shade50,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.image_outlined, color: Colors.teal),
                  ),
                  const SizedBox(width: 12),
                  const Text(
                    'Driver license',
                    style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    // TODO: pick image from gallery/camera later
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Upload document - coming soon')),
                    );
                  },
                  icon: const Icon(Icons.upload_file_outlined, size: 18, color: Colors.black87),
                  label: const Text('Upload', style: TextStyle(color: Colors.black87, fontWeight: FontWeight.w600)),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    side: BorderSide(color: Colors.grey.shade300),
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Password
              TextFormField(
                controller: passwordController,
                obscureText: obscurePassword,
                decoration: _fieldDecoration(
                  'Password',
                  Icons.lock_outline,
                  suffixIcon: IconButton(
                    icon: Icon(obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined),
                    onPressed: () => setState(() => obscurePassword = !obscurePassword),
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) return 'Please enter your password';
                  if (value.length < 8) return 'Password must be at least 8 characters';
                  if (!value.contains(RegExp(r'[A-Z]'))) return 'Must contain at least 1 uppercase letter';
                  if (!value.contains(RegExp(r'[0-9]'))) return 'Must contain at least 1 number';
                  if (!value.contains(RegExp(r'[!@#$%^&*(),.?":{}|<>]'))) return 'Must contain at least 1 symbol';
                  return null;
                },
              ),
              const SizedBox(height: 14),

              // Confirm password
              TextFormField(
                controller: confirmPasswordController,
                obscureText: obscureConfirmPassword,
                decoration: _fieldDecoration(
                  'Confirm password',
                  Icons.lock_outline,
                  suffixIcon: IconButton(
                    icon: Icon(obscureConfirmPassword ? Icons.visibility_outlined : Icons.visibility_off_outlined),
                    onPressed: () => setState(() => obscureConfirmPassword = !obscureConfirmPassword),
                  ),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) return 'Please confirm your password';
                  if (value != passwordController.text) return 'Passwords do not match';
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // Validate address button + hint (disabled for now)
              /*
              // Validate address button
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: isValidatingAddress ? null : _validateAddress,
                  icon: isValidatingAddress
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : Icon(
                          isAddressValidated ? Icons.check_circle : Icons.check_circle_outline,
                          size: 18,
                          color: isAddressValidated ? Colors.green : Colors.lightBlue[900],
                        ),
                  label: Text(
                    isAddressValidated ? 'Address validated' : 'Validate address',
                    style: TextStyle(
                      color: isAddressValidated ? Colors.green : Colors.lightBlue[900],
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    side: BorderSide(
                      color: isAddressValidated ? Colors.green : Colors.grey.shade300,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),

              Text(
                isAddressValidated && validatedDisplayAddress != null
                    ? 'Pinned location: $validatedDisplayAddress'
                    : 'Enter the pickup address, then tap Validate address to pin the location automatically.',
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
              */
              const SizedBox(height: 30),

              // Register button
              SizedBox(
                height: 50,
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: isSubmitting ? null : _handleRegister,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.lightBlue[900],
                    disabledBackgroundColor: Colors.grey.shade300,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: Text(
                    isSubmitting ? 'Submitting...' : 'Register',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: isSubmitting ? Colors.grey[600] : Colors.white,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  InputDecoration _fieldDecoration(String label, IconData icon, {Widget? suffixIcon}) {
    return InputDecoration(
      hintText: label,
      prefixIcon: Icon(icon, color: Colors.grey),
      suffixIcon: suffixIcon,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(color: Colors.grey.shade300),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Colors.teal, width: 2),
      ),
    );
  }
}