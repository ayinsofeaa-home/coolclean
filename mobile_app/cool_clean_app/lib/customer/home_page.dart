import 'package:flutter/material.dart';
import 'booking_page.dart';
import 'notification_page.dart';
import 'settings_page.dart';
import 'create_booking.dart';
import '../login_page.dart';



class HomePage extends StatefulWidget {
  final String userName;
  const HomePage({super.key, required this.userName});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _selectedIndex = 0;
  bool _isEnglish = true;

  final List<String> _tabTitles = ['Home', 'Bookings', 'Alerts', 'Settings'];

  void _onTabTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  void _handleLogout() {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => const LoginPage()),
          (route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: _buildBody(),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        onTap: _onTabTapped,
        selectedItemColor: Colors.blue.shade600,
        unselectedItemColor: Colors.grey,
        type: BottomNavigationBarType.fixed,
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.receipt_long), label: 'Bookings'),
          BottomNavigationBarItem(icon: Icon(Icons.notifications), label: 'Alerts'),
          BottomNavigationBarItem(icon: Icon(Icons.settings), label: 'Settings'),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_selectedIndex == 0) {
      return _buildHomeContent();
    } else if (_selectedIndex == 1) {
      return const BookingPage();
    } else if (_selectedIndex == 2) {
      return const NotificationsPage();
    } else if (_selectedIndex == 3) {
      return const SettingsPage();
    } else {
      return Center(
        child: Text('${_tabTitles[_selectedIndex]} page coming soon'),
      );
    }
  }

  Widget _buildHomeContent() {
    return SafeArea(
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          // Top bar: title, EN/BM toggle, logout
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Customer Home',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
              ),
              Row(
                children: [
                  _buildLangButton('EN', true),
                  _buildLangButton('BM', false),
                  const SizedBox(width: 12),
                  IconButton(
                    icon: const Icon(Icons.logout),
                    onPressed: _handleLogout,
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Greeting with logo
          Row(
            children: [
              Image.asset('Assets/logo/CoolCleanLogo.png', height: 60),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Hi, ${widget.userName}',
                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                    const Text(
                      'Ready to schedule your laundry pickup?',
                      style: TextStyle(color: Colors.grey, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // Stats cards: Total spending, Customer wallet
          Row(
            children: [
              Expanded(
                child: _buildStatCard(
                  icon: Icons.receipt_long,
                  label: 'Total spending',
                  value: 'RM 100.00',
                  color: Colors.black87,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildStatCard(
                  icon: Icons.account_balance_wallet_outlined,
                  label: 'Customer wallet',
                  value: 'RM 0.00',
                  color: Colors.green.shade700,
                  backgroundColor: Colors.green.shade50,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Active booking card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.grey.shade100,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 22,
                  backgroundColor: Colors.blue.shade50,
                  child: Icon(Icons.local_laundry_service, color: Colors.lightBlue.shade900),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: const [
                      Text('CC-1785944694530', style: TextStyle(fontWeight: FontWeight.bold)),
                      SizedBox(height: 2),
                      Text('Paid Waiting Driver | RM 30.00', style: TextStyle(color: Colors.grey, fontSize: 12)),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const CreateBookingPage(),
                      ),
                    );
                  },
                  child: const Text('Create booking'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Services section
          const Text(
            'Services',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            childAspectRatio: 1.3,
            children: [
              _buildServiceCard(icon: Icons.local_laundry_service, title: 'Wash', subtitle: 'RM 12.00', enabled: true),
              _buildServiceCard(icon: Icons.dry_cleaning, title: 'Dry', subtitle: 'RM 10.00', enabled: true),
              _buildServiceCard(icon: Icons.checkroom, title: 'Folding', subtitle: 'RM 7.OO', enabled: true),
              _buildServiceCard(icon: Icons.iron, title: 'Ironing', subtitle: 'RM 13.00', enabled: true),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildLangButton(String label, bool isEnglishOption) {
    final bool isSelected = _isEnglish == isEnglishOption;
    return GestureDetector(
      onTap: () => setState(() => _isEnglish = isEnglishOption),
      child: Container(
        margin: const EdgeInsets.only(left: 4),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? Colors.blue.shade800 : Colors.grey.shade200,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : Colors.black54,
            fontWeight: FontWeight.bold,
            fontSize: 11,
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required IconData icon,
    required String label,
    required String value,
    required Color color,
    Color? backgroundColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: backgroundColor ?? Colors.grey.shade100,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color),
          const SizedBox(height: 8),
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }

  Widget _buildServiceCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool enabled,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: enabled ? Colors.blue.shade50 : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: enabled ? Colors.blue.shade400 : Colors.grey),
          const Spacer(),
          Text(title, style: TextStyle(fontWeight: FontWeight.bold, color: enabled ? Colors.black87 : Colors.grey)),
          Text(subtitle, style: TextStyle(fontSize: 12, color: enabled ? Colors.black54 : Colors.grey)),
        ],
      ),
    );
  }
}