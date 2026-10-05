import 'package:flutter/material.dart';
import 'homepage.dart';
import 'job_page.dart';
import 'alerts_page.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  int currentTab = 3; // Profile tab

  // Controllers untuk field
  final TextEditingController nameController =
  TextEditingController(text: 'Danish Fitri');
  final TextEditingController emailController =
  TextEditingController(text: 'danish@example.com');
  final TextEditingController phoneController =
  TextEditingController(text: '013-555 0101');
  final TextEditingController plateController =
  TextEditingController(text: 'WXY 1234');

  final TextEditingController currentPasswordController =
  TextEditingController();
  final TextEditingController newPasswordController =
  TextEditingController();
  final TextEditingController confirmPasswordController =
  TextEditingController();

  String? selectedVehicle = 'Motorcycle';

  // TODO: replace dengan token Firebase sebenar dari FirebaseMessaging.instance.getToken()
  final String fcmToken =
      'f_Zy7qXGQYy9W5jOQZjJRJ:APA91bH0bnDoEOHtC7igUx9nFxyTPxhHollS2BfLwCKU5Gdnf6mksbDRu_24OYeRjV5qdGflQ7Ocbs9PkcXRYjV3ajt8dyYBaKSJ46Qp2CeeFPtS7SFXQRQ';

  @override
  void dispose() {
    nameController.dispose();
    emailController.dispose();
    phoneController.dispose();
    plateController.dispose();
    currentPasswordController.dispose();
    newPasswordController.dispose();
    confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xffF5F6FA),

      //APPBAR

      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: Image.asset(
              'Assets/logo/back.png',
              width: 20,
              height: 20,
              color: Colors.lightBlue[900]
          ),
          onPressed: () {
            Navigator.pop(context);
          },
        ),
        title: Text(
          'Profile',
          style: TextStyle(
            color: Colors.lightBlue[900],
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          //LOGOUT ICON
          GestureDetector(
            onTap: () {
              Navigator.pop(context);
            },
            child: Container(
              margin: const EdgeInsets.only(right: 16, left: 4),
              child: Icon(Icons.logout, color: Colors.lightBlue[900]),
            ),
          ),
        ],
      ),

      //BODY

      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [

            _buildLabel('Full name'),
            _buildTextField(
              controller: nameController,
              icon: Icons.person_outline,
            ),
            const SizedBox(height: 14),

            _buildLabel('Email'),
            _buildTextField(
              controller: emailController,
              icon: Icons.email_outlined,
              enabled: false, // email biasanya tak boleh edit
            ),
            const SizedBox(height: 14),

            _buildLabel('Phone'),
            _buildTextField(
              controller: phoneController,
              icon: Icons.call_outlined,
              keyboardType: TextInputType.phone,
            ),
            const SizedBox(height: 24),

            Text(
              'Vehicle',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Colors.lightBlue[900],
              ),
            ),
            const SizedBox(height: 12),

            _buildLabel('Vehicle type'),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButtonFormField<String>(
                  value: selectedVehicle,
                  decoration: const InputDecoration(
                    border: InputBorder.none,
                  ),
                  items: const [
                    DropdownMenuItem(
                      value: 'Motorcycle',
                      child: Text('Motorcycle'),
                    ),
                    DropdownMenuItem(
                      value: 'Car',
                      child: Text('Car'),
                    ),
                    DropdownMenuItem(
                      value: 'Van',
                      child: Text('Van'),
                    ),
                  ],
                  onChanged: (value) {
                    setState(() {
                      selectedVehicle = value;
                    });
                  },
                ),
              ),
            ),
            const SizedBox(height: 14),

            _buildLabel('Vehicle plate'),
            _buildTextField(
              controller: plateController,
              icon: Icons.badge_outlined,
            ),
            const SizedBox(height: 20),

            //SAVE PROFILE BUTTON
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  // TODO: hantar data profile ni ke server/database
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.lightBlue[900],
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: const Text(
                  'Save Profile',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 15,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 24),

            Text(
              'Change Password',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Colors.lightBlue[900],
              ),
            ),
            const SizedBox(height: 12),

            _buildTextField(
              controller: currentPasswordController,
              icon: Icons.lock_outline,
              hint: 'Current password',
              obscure: true,
            ),
            const SizedBox(height: 12),

            _buildTextField(
              controller: newPasswordController,
              icon: Icons.lock_reset_outlined,
              hint: 'New password',
              obscure: true,
            ),
            const SizedBox(height: 12),

            _buildTextField(
              controller: confirmPasswordController,
              icon: Icons.lock_reset_outlined,
              hint: 'Confirm new password',
              obscure: true,
            ),
            const SizedBox(height: 16),

            //CHANGE PASSWORD BUTTON
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: () {
                  // TODO: validate & hantar request tukar password
                },
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  side: BorderSide(color: Colors.grey.shade300),
                ),
                child: Text(
                  'Change Password',
                  style: TextStyle(
                    color: Colors.lightBlue[900],
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 20),

            //FCM TOKEN INFO BOX (untuk testing push notification)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.teal.shade50,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Push notification is enabled for Firebase testing. Use the token below for Send test message in Firebase Console.',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: Colors.teal[800],
                    ),
                  ),
                  const SizedBox(height: 8),
                  SelectableText(
                    fcmToken,
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey[700],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),

      //BOTTOM NAVIGATION

      bottomNavigationBar: BottomNavigationBar(
        currentIndex: currentTab,
        selectedItemColor: Colors.lightBlue[900],
        unselectedItemColor: Colors.grey,
        onTap: (index) async {
          if (index == currentTab) return;

          if (index == 0) {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const Homepage()),
            );
          } else if (index == 1) {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const JobPage()),
            );
          } else if (index == 2) {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const AlertPage()),
            );
          }
        },
        items: [
          BottomNavigationBarItem(
            icon: Image.asset(
              'Assets/logo/homepage.png',
              width: 24,
              height: 24,
              color: currentTab == 0 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Image.asset(
              'Assets/logo/job.png',
              width: 24,
              height: 24,
              color: currentTab == 1 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Jobs',
          ),
          BottomNavigationBarItem(
            icon: Image.asset(
              'Assets/logo/alerts.png',
              width: 24,
              height: 24,
              color: currentTab == 2 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Alerts',
          ),
          BottomNavigationBarItem(
            icon: Image.asset(
              'Assets/logo/profile.png',
              width: 24,
              height: 24,
              color: currentTab == 3 ? Colors.lightBlue[900] : Colors.grey,
            ),
            label: 'Profile',
          ),
        ],
      ),
    );
  }

  // Label kecil kat atas setiap field
  Widget _buildLabel(String text) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 4),
      child: Text(
        text,
        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
      ),
    );
  }

  // Text field dengan icon kat depan
  Widget _buildTextField({
    required TextEditingController controller,
    required IconData icon,
    String? hint,
    bool enabled = true,
    bool obscure = false,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: TextField(
        controller: controller,
        enabled: enabled,
        obscureText: obscure,
        keyboardType: keyboardType,
        style: TextStyle(color: enabled ? Colors.black87 : Colors.grey),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: TextStyle(color: Colors.grey[500]),
          prefixIcon: Icon(icon, color: Colors.grey[700], size: 20),
          border: InputBorder.none,
          contentPadding:
          const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
        ),
      ),
    );
  }

  // Button kecil EN / BM
  Widget _buildLangButton(String label, bool selected) {
    return GestureDetector(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: selected ? Colors.lightBlue[900] : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: selected ? Colors.white : Colors.grey[700],
            fontWeight: FontWeight.bold,
            fontSize: 12,
          ),
        ),
      ),
    );
  }
}