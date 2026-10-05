import 'package:flutter/material.dart';

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

  void _handleRegister() {
    if (_formKey.currentState!.validate()) {
      // TODO: send form data to backend (API) later
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Driver registration submitted for approval - coming soon')),
      );
    }
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
              ),
              const SizedBox(height: 14),

              // Service postcode
              TextFormField(
                controller: postcodeController,
                keyboardType: TextInputType.number,
                decoration: _fieldDecoration('Service postcode', Icons.local_post_office_outlined),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your postcode' : null,
              ),
              const SizedBox(height: 14),

              // Service city
              TextFormField(
                controller: cityController,
                decoration: _fieldDecoration('Service city', Icons.location_city_outlined),
                validator: (value) => (value == null || value.isEmpty) ? 'Please enter your city' : null,
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

              // Validate address button
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    // TODO: connect this to a real geocoding/map API later
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Address validated (placeholder)')),
                    );
                  },
                  icon: Icon(Icons.check_circle_outline, size: 18, color: Colors.lightBlue[900]),
                  label: Text(
                    'Validate address',
                    style: TextStyle(color: Colors.lightBlue[900], fontWeight: FontWeight.bold),
                  ),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    side: BorderSide(color: Colors.grey.shade300),
                  ),
                ),
              ),
              const SizedBox(height: 12),

              Text(
                'Enter the pickup address, then tap Validate address to pin the location automatically.',
                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
              ),
              const SizedBox(height: 30),

              // Register button
              SizedBox(
                height: 50,
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _handleRegister,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.lightBlue[900],
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text(
                    'Register',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
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