import 'package:flutter/material.dart';
import 'homepage.dart';
import 'job_page.dart';
import 'profile_page.dart';

// Model senang untuk satu alert
class AlertItem {
  final String jobCode;
  final String status;
  final String serviceType;

  AlertItem({
    required this.jobCode,
    required this.status,
    required this.serviceType,
  });
}

class AlertPage extends StatefulWidget {
  const AlertPage({super.key});

  @override
  State<AlertPage> createState() => _AlertPageState();
}

class _AlertPageState extends State<AlertPage> {
  int currentTab = 2; // Alerts tab

  // TODO: nanti boleh replace list ni dengan data dari server/database
  final List<AlertItem> alerts = [
    AlertItem(
      jobCode: 'CC-1045',
      status: 'Completed',
      serviceType: 'Laundry service',
    ),
    AlertItem(
      jobCode: 'CC-1048',
      status: 'Completed',
      serviceType: 'Laundry service',
    ),
  ];

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
          'Alerts',
          style: TextStyle(
            color: Colors.lightBlue[900],
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        actions: [
          //LOGOUT / EXIT ICON
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

      //BODY - LIST OF ALERTS

      body: alerts.isEmpty
          ? Center(
        child: Text(
          'No alerts right now.',
          style: TextStyle(fontSize: 14, color: Colors.grey[600]),
        ),
      )
          : ListView.builder(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        itemCount: alerts.length,
        itemBuilder: (context, index) {
          final alert = alerts[index];
          return Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: Colors.grey.withOpacity(0.15),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: Colors.teal.shade50,
                    borderRadius: BorderRadius.circular(30),
                  ),
                  child: Image.asset(
                    'Assets/logo/alerts.png',
                    color: Colors.teal,
                    width: 22,
                    height: 22,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        alert.jobCode,
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: Colors.lightBlue[900],
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${alert.status} • ${alert.serviceType}',
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),

      //BOTTOM NAVIGATION

      bottomNavigationBar: BottomNavigationBar(
        currentIndex: currentTab,
        selectedItemColor: Colors.lightBlue[900],
        unselectedItemColor: Colors.grey,
        onTap: (index) async {
          if (index == currentTab) return; // dah kat page ni, xyah buat apa2

          if (index == 0) {
            // balik ke Home
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const Homepage()),
            );
          } else if (index == 1) {
            // pergi ke Jobs
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const JobPage()),
            );
          } else if (index == 3) {
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (context) => const ProfilePage()),
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
}