import 'package:flutter/material.dart';
import 'login_page.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Cool Clean',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(fontFamily: 'Poppins'),
      // Single shared login page for both customers and drivers.
      // It reads the "role" field from the login API response and routes
      // to lib/customer/home_page.dart or lib/driver/homepage.dart.
      home: const LoginPage(),
    );
  }
}
