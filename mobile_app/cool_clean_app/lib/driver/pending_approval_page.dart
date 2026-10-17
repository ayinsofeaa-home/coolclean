import 'package:flutter/material.dart';
import '../login_page.dart';

/// Shown instead of the normal driver home page when a driver's account
/// still has STATUS = 'PENDING_APPROVAL' on the backend (i.e. an admin
/// hasn't approved or rejected their application yet).
class PendingApprovalPage extends StatelessWidget {
  final String driverName;

  const PendingApprovalPage({super.key, required this.driverName});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.hourglass_top_rounded, size: 72, color: Colors.amber[700]),
              const SizedBox(height: 24),
              Text(
                'Application under review',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.lightBlue[900],
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'Hi $driverName, thanks for registering as a CoolClean driver. '
                    'An admin is reviewing your application. You\'ll be able to log '
                    'in and start accepting jobs once it\'s approved.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 14, color: Colors.grey[600], height: 1.5),
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: OutlinedButton(
                  onPressed: () {
                    Navigator.pushAndRemoveUntil(
                      context,
                      MaterialPageRoute(builder: (context) => const LoginPage()),
                          (route) => false,
                    );
                  },
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    side: BorderSide(color: Colors.grey.shade300),
                  ),
                  child: Text(
                    'Back to login',
                    style: TextStyle(color: Colors.lightBlue[900], fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}