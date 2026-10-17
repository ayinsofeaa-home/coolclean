import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../app_config.dart';
import 'accepted_job_page.dart';
import 'homepage.dart';
import 'job_page.dart';
import 'profile_page.dart';

/// Alerts page = the list of every order this driver has COMPLETED.
/// (Orders that are still in progress stay on the Jobs page, under "My jobs".)
class AlertPage extends StatefulWidget {
  const AlertPage({super.key});

  @override
  State<AlertPage> createState() => _AlertPageState();
}

class _AlertPageState extends State<AlertPage> {
  int currentTab = 2; // Alerts tab

  List<dynamic> completedJobs = [];
  bool _isLoading = true;
  String? _loadError;

  static const Map<String, String> _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  @override
  void initState() {
    super.initState();
    _loadCompletedJobs();
  }

  /// Reads the server's error message (a crashed server returns HTML, not JSON).
  String _messageFrom(http.Response response, String fallback) {
    try {
      final data = jsonDecode(response.body);
      if (data is Map && data['message'] != null) return data['message'].toString();
    } catch (_) {}
    return '$fallback (server error ${response.statusCode}).';
  }

  /// Loads this driver's jobs and keeps only the completed ones.
  Future<void> _loadCompletedJobs({bool showSpinner = true}) async {
    final userId = AppSession.userId;
    if (userId == null) {
      setState(() {
        _isLoading = false;
        _loadError = 'Please log in again.';
      });
      return;
    }

    if (showSpinner) {
      setState(() {
        _isLoading = true;
        _loadError = null;
      });
    }

    try {
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/mobile/drivers/$userId/jobs'),
        headers: _headers,
      );
      if (!mounted) return;

      if (response.statusCode == 200) {
        final all = jsonDecode(response.body) as List;
        setState(() {
          completedJobs = all.where((j) => j['status'] == 'COMPLETED').toList();
          _isLoading = false;
          _loadError = null;
        });
      } else {
        debugPrint('LOAD ALERTS: ${response.statusCode} ${response.body}');
        setState(() {
          _isLoading = false;
          _loadError = _messageFrom(response, 'Could not load your completed orders');
        });
      }
    } catch (e) {
      debugPrint('LOAD ALERTS ERROR: $e');
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _loadError = 'Unable to connect to the server.';
      });
    }
  }

  Future<void> _openJobDetails(Map<String, dynamic> job) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => AcceptedJobPage(job: job, justAccepted: false),
      ),
    );
  }

  Widget _buildBody() {
    const scrollPhysics = AlwaysScrollableScrollPhysics();

    if (_isLoading) {
      return ListView(
        physics: scrollPhysics,
        children: const [
          SizedBox(height: 140),
          Center(child: CircularProgressIndicator()),
        ],
      );
    }

    if (_loadError != null) {
      return ListView(
        physics: scrollPhysics,
        padding: const EdgeInsets.symmetric(horizontal: 24),
        children: [
          const SizedBox(height: 120),
          Text(
            _loadError!,
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 14, color: Colors.grey[600]),
          ),
          Center(
            child: TextButton(
              onPressed: _loadCompletedJobs,
              child: const Text('Try again'),
            ),
          ),
        ],
      );
    }

    if (completedJobs.isEmpty) {
      return ListView(
        physics: scrollPhysics,
        padding: const EdgeInsets.symmetric(horizontal: 24),
        children: [
          const SizedBox(height: 140),
          Text(
            'No completed orders yet.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 14, color: Colors.grey[600]),
          ),
        ],
      );
    }

    return ListView.builder(
      physics: scrollPhysics,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      itemCount: completedJobs.length,
      itemBuilder: (context, index) {
        final job = completedJobs[index] as Map<String, dynamic>;
        final total = (job['totalAmount'] as num?) ?? 0;

        return Container(
          margin: const EdgeInsets.only(bottom: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            boxShadow: const [
              BoxShadow(color: Colors.black12, blurRadius: 8, offset: Offset(0, 2)),
            ],
          ),
          child: InkWell(
            borderRadius: BorderRadius.circular(14),
            onTap: () => _openJobDetails(job),
            child: Padding(
              padding: const EdgeInsets.all(14),
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
                          (job['bookingNo'] ?? '').toString(),
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: Colors.lightBlue[900],
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Completed • ${(job['serviceSummary'] ?? 'Laundry service').toString()}',
                          style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'RM ${total.toStringAsFixed(2)}',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: Colors.teal,
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
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

      //BODY - COMPLETED ORDERS (pull down to refresh)

      body: RefreshIndicator(
        onRefresh: () => _loadCompletedJobs(showSpinner: false),
        child: _buildBody(),
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
            // pergi ke Profile
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