import 'package:flutter/material.dart';
import 'customer/register_customer_page.dart';
import 'driver/driver_register_page.dart';

/// SHARED "choose your role" page - used by both customer and driver
/// registration flows. Tapping a card opens the matching form.
class RegisterPage extends StatelessWidget {
  const RegisterPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back, color: Colors.lightBlue[900]),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Create Account',
          style: TextStyle(
            color: Colors.lightBlue[900],
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 20),
            Center(
              child: Image.asset(
                'Assets/logo/CoolCleanLogo.png',
                width: 130,
                height: 130,
              ),
            ),
            const SizedBox(height: 30),

            _buildRoleCard(
              context,
              icon: Icons.person_add_alt_1,
              title: 'Register as Customer',
              subtitle: 'Book laundry pickup, track progress, and view history.',
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const RegisterCustomerPage()),
              ),
            ),
            const SizedBox(height: 20),

            _buildRoleCard(
              context,
              icon: Icons.local_shipping_outlined,
              title: 'Register as Driver',
              subtitle: 'Submit your driver profile for admin approval.',
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const DriverRegister()),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRoleCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16.0),
        decoration: BoxDecoration(
          color: Colors.grey.shade100,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: const BoxDecoration(color: Colors.transparent, shape: BoxShape.circle),
              child: CircleAvatar(
                radius: 18,
                backgroundColor: Colors.teal.shade50,
                child: Icon(icon, color: Colors.teal, size: 20),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                      color: Colors.lightBlue[900],
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(subtitle, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: Colors.teal),
          ],
        ),
      ),
    );
  }
}
