import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../app_config.dart';
import 'accepted_job_page.dart';

/// The driver's "Bookings" page.
///  - New orders: paid customer bookings waiting for a driver (Accept / Reject)
///  - My jobs:    jobs this driver already accepted (tap one to see the details)
class JobPage extends StatefulWidget {
  const JobPage({super.key});

  @override
  State<JobPage> createState() => _JobPageState();
}

class _JobPageState extends State<JobPage> {
  List<dynamic> newOrders = [];
  List<dynamic> myJobs = [];
  bool _isLoading = true;
  String? _loadError;
  final Set<int> _busyIds = {}; // orders with an accept/reject request running

  static const Map<String, String> _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  // An offline driver doesn't receive new orders (same rule as the Home page).
  bool get _isOnline => AppSession.driverAvailability == 'ONLINE';

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  /// Reads the server's error message (a crashed server returns HTML, not JSON).
  String _messageFrom(http.Response response, String fallback) {
    try {
      final data = jsonDecode(response.body);
      if (data is Map && data['message'] != null) return data['message'].toString();
    } catch (_) {}
    return '$fallback (server error ${response.statusCode}).';
  }

  String _statusLabel(dynamic status) {
    final text = (status ?? '').toString().replaceAll('_', ' ').toLowerCase();
    if (text.isEmpty) return '';
    return text[0].toUpperCase() + text.substring(1);
  }

  MaterialColor _statusColor(String status) {
    switch (status) {
      case 'PENDING_PAYMENT':
        return Colors.orange;
      case 'PAID_WAITING_DRIVER':
        return Colors.blue;
      case 'CANCELLED':
        return Colors.red;
      case 'COMPLETED':
        return Colors.green;
      default:
        return Colors.teal; // accepted / picked up / washing / etc.
    }
  }

  String _jobAddress(dynamic job) {
    final parts = [job['pickupAddress'], job['pickupCity']]
        .where((p) => p != null && p.toString().trim().isNotEmpty)
        .map((p) => p.toString())
        .toList();
    return parts.isEmpty ? 'No address provided' : parts.join(', ');
  }

  BoxDecoration _cardDecoration() {
    return BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      boxShadow: const [
        BoxShadow(color: Colors.black12, blurRadius: 8, offset: Offset(0, 2)),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // Server calls
  // ---------------------------------------------------------------------------

  /// Loads the new orders and this driver's own jobs.
  Future<void> _loadOrders({bool showSpinner = true}) async {
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
      final results = await Future.wait([
        http.get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/drivers/$userId/jobs/available'),
          headers: _headers,
        ),
        http.get(
          Uri.parse('${ApiConfig.baseUrl}/mobile/drivers/$userId/jobs'),
          headers: _headers,
        ),
      ]);
      if (!mounted) return;

      if (results[0].statusCode != 200 || results[1].statusCode != 200) {
        final failed = results[0].statusCode != 200 ? results[0] : results[1];
        debugPrint('LOAD ORDERS: ${failed.statusCode} ${failed.body}');
        setState(() {
          _isLoading = false;
          _loadError = _messageFrom(failed, 'Could not load your orders');
        });
        return;
      }

      setState(() {
        newOrders = _isOnline ? (jsonDecode(results[0].body) as List) : [];
        // Completed orders are listed on the Alerts page instead.
        myJobs = (jsonDecode(results[1].body) as List)
            .where((j) => j['status'] != 'COMPLETED')
            .toList();
        _isLoading = false;
        _loadError = null;
      });
    } catch (e) {
      debugPrint('LOAD ORDERS ERROR: $e');
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _loadError = 'Unable to connect to the server.';
      });
    }
  }

  /// Accept: assign the order to this driver, then open its details.
  Future<void> _acceptOrder(Map<String, dynamic> job) async {
    final userId = AppSession.userId;
    if (userId == null) {
      _showMessage('Please log in again.');
      return;
    }
    final jobId = (job['id'] as num).toInt();

    setState(() => _busyIds.add(jobId));
    Map<String, dynamic>? acceptedJob;

    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/mobile/drivers/$userId/jobs/$jobId/accept'),
        headers: _headers,
      );
      debugPrint('ACCEPT: ${response.statusCode} ${response.body}');
      if (!mounted) return;

      if (response.statusCode == 200) {
        acceptedJob = Map<String, dynamic>.from(jsonDecode(response.body));
      } else {
        _showMessage(_messageFrom(response, 'Could not accept this order'));
        _loadOrders(showSpinner: false); // e.g. another driver already took it
      }
    } catch (e) {
      debugPrint('ACCEPT ERROR: $e');
      if (mounted) _showMessage('Unable to connect to the server.');
    } finally {
      if (mounted) setState(() => _busyIds.remove(jobId));
    }

    if (acceptedJob != null && mounted) {
      final accepted = acceptedJob;
      setState(() => newOrders.removeWhere((j) => j['id'] == job['id']));
      await Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => AcceptedJobPage(job: accepted)),
      );
      if (mounted) _loadOrders(showSpinner: false);
    }
  }

  /// Reject: hide this order for THIS driver only. Other drivers still see it.
  Future<void> _rejectOrder(Map<String, dynamic> job) async {
    final userId = AppSession.userId;
    if (userId == null) {
      _showMessage('Please log in again.');
      return;
    }
    final jobId = (job['id'] as num).toInt();

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Reject this order?'),
        content: const Text(
          'It will be removed from your list. Other drivers can still accept it.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text('Reject', style: TextStyle(color: Colors.red[700])),
          ),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;

    setState(() => _busyIds.add(jobId));

    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/mobile/drivers/$userId/jobs/$jobId/reject'),
        headers: _headers,
      );
      debugPrint('REJECT: ${response.statusCode} ${response.body}');
      if (!mounted) return;

      if (response.statusCode == 200) {
        setState(() => newOrders.removeWhere((j) => j['id'] == job['id']));
      } else {
        _showMessage(_messageFrom(response, 'Could not reject this order'));
      }
    } catch (e) {
      debugPrint('REJECT ERROR: $e');
      if (mounted) _showMessage('Unable to connect to the server.');
    } finally {
      if (mounted) setState(() => _busyIds.remove(jobId));
    }
  }

  Future<void> _openJobDetails(Map<String, dynamic> job) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => AcceptedJobPage(job: job, justAccepted: false),
      ),
    );
    if (mounted) _loadOrders(showSpinner: false);
  }

  // ---------------------------------------------------------------------------
  // UI
  // ---------------------------------------------------------------------------

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: Colors.grey.shade100,
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          leading: IconButton(
            icon: Image.asset(
              'Assets/logo/back.png',
              width: 20,
              height: 20,
              color: Colors.lightBlue[900],
            ),
            onPressed: () => Navigator.pop(context),
          ),
          title: Text(
            'Bookings',
            style: TextStyle(
              color: Colors.lightBlue[900],
              fontSize: 18,
              fontWeight: FontWeight.w600,
            ),
          ),
          bottom: TabBar(
            labelColor: Colors.lightBlue[900],
            unselectedLabelColor: Colors.grey,
            indicatorColor: Colors.teal,
            tabs: [
              Tab(text: 'New orders (${newOrders.length})'),
              Tab(text: 'My jobs (${myJobs.length})'),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            _buildNewOrdersTab(),
            _buildMyJobsTab(),
          ],
        ),
      ),
    );
  }

  /// Pull-to-refresh list wrapper used by both tabs.
  Widget _scrollable(List<Widget> children) {
    return RefreshIndicator(
      onRefresh: () => _loadOrders(showSpinner: false),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(16),
        children: children,
      ),
    );
  }

  Widget _stateMessage(IconData icon, String text, {bool showRetry = false}) {
    return Padding(
      padding: const EdgeInsets.only(top: 90, left: 24, right: 24),
      child: Column(
        children: [
          Icon(icon, size: 40, color: Colors.grey),
          const SizedBox(height: 12),
          Text(
            text,
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 13, color: Colors.grey[600]),
          ),
          if (showRetry)
            TextButton(
              onPressed: _loadOrders,
              child: const Text('Try again'),
            ),
        ],
      ),
    );
  }

  Widget _buildNewOrdersTab() {
    if (_isLoading) {
      return _scrollable(const [
        SizedBox(height: 120),
        Center(child: CircularProgressIndicator()),
      ]);
    }
    if (_loadError != null) {
      return _scrollable([_stateMessage(Icons.error_outline, _loadError!, showRetry: true)]);
    }
    if (!_isOnline) {
      return _scrollable([
        _stateMessage(
          Icons.power_settings_new,
          'You are offline. Go online on the Home page to receive new orders.',
        ),
      ]);
    }
    if (newOrders.isEmpty) {
      return _scrollable([
        _stateMessage(
          Icons.inbox_outlined,
          'No new orders right now. Pull down to refresh.',
        ),
      ]);
    }
    return _scrollable([
      for (final order in newOrders) _buildNewOrderCard(order as Map<String, dynamic>),
    ]);
  }

  Widget _buildMyJobsTab() {
    if (_isLoading) {
      return _scrollable(const [
        SizedBox(height: 120),
        Center(child: CircularProgressIndicator()),
      ]);
    }
    if (_loadError != null) {
      return _scrollable([_stateMessage(Icons.error_outline, _loadError!, showRetry: true)]);
    }
    if (myJobs.isEmpty) {
      return _scrollable([
        _stateMessage(
          Icons.work_outline,
          'No jobs in progress. Accepted orders appear here, and finished ones move to the Alerts page.',
        ),
      ]);
    }
    return _scrollable([
      for (final job in myJobs) _buildMyJobCard(job as Map<String, dynamic>),
    ]);
  }

  /// A customer's paid booking waiting for a driver, with Reject / Accept.
  Widget _buildNewOrderCard(Map<String, dynamic> job) {
    final jobId = (job['id'] as num).toInt();
    final busy = _busyIds.contains(jobId);
    final total = (job['totalAmount'] as num?) ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: _cardDecoration(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  (job['bookingNo'] ?? '').toString(),
                  style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                ),
              ),
              Text(
                'RM ${total.toStringAsFixed(2)}',
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: Colors.teal,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            (job['serviceSummary'] ?? 'Laundry service').toString(),
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.bold,
              color: Colors.lightBlue[900],
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(Icons.person_outline, size: 16, color: Colors.grey[600]),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  (job['customerName'] ?? '-').toString(),
                  style: TextStyle(fontSize: 13, color: Colors.grey[800]),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(Icons.location_on_outlined, size: 16, color: Colors.grey[600]),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  _jobAddress(job),
                  style: TextStyle(fontSize: 13, color: Colors.grey[800]),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: busy ? null : () => _rejectOrder(job),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.red[700],
                    side: BorderSide(color: Colors.red.shade200),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text('Reject order'),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: busy ? null : () => _acceptOrder(job),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.teal,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: busy
                      ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                      : const Text('Accept order'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// A job this driver already accepted. Tap to open the details.
  Widget _buildMyJobCard(Map<String, dynamic> job) {
    final status = (job['status'] ?? '').toString();
    final color = _statusColor(status);
    final total = (job['totalAmount'] as num?) ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: _cardDecoration(),
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: () => _openJobDetails(job),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      (job['bookingNo'] ?? '').toString(),
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: color.shade100,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      _statusLabel(status),
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: color.shade800,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                (job['serviceSummary'] ?? 'Laundry service').toString(),
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: Colors.lightBlue[900],
                ),
              ),
              const SizedBox(height: 6),
              Text(
                _jobAddress(job),
                style: TextStyle(fontSize: 13, color: Colors.grey[700]),
              ),
              const SizedBox(height: 6),
              Text(
                'RM ${total.toStringAsFixed(2)}',
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Colors.teal,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}