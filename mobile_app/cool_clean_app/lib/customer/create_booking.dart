import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../app_config.dart';

/// Data model for a single booking entry (built from the server's JSON).
class Booking {
  final int id;
  final String orderId;
  final String serviceName;
  final double price;
  final String status; // raw server status, e.g. PAID_WAITING_DRIVER
  final String address;

  const Booking({
    required this.id,
    required this.orderId,
    required this.serviceName,
    required this.price,
    required this.status,
    required this.address,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    final addressParts = [
      json['pickupAddress'],
      json['pickupPostcode'],
      json['pickupCity'],
      json['pickupState'],
    ].where((p) => p != null && p.toString().trim().isNotEmpty).map((p) => p.toString());

    return Booking(
      id: (json['id'] as num).toInt(),
      orderId: (json['bookingNo'] ?? '').toString(),
      serviceName: (json['serviceSummary'] ?? 'Laundry service').toString(),
      price: (json['totalAmount'] as num?)?.toDouble() ?? 0,
      status: (json['status'] ?? '').toString(),
      address: addressParts.join(', '),
    );
  }

  /// "PAID_WAITING_DRIVER" -> "Paid waiting driver"
  String get statusLabel {
    final text = status.replaceAll('_', ' ').toLowerCase();
    if (text.isEmpty) return '';
    return text[0].toUpperCase() + text.substring(1);
  }

  /// A customer can only cancel before a driver accepts.
  bool get canCancel => status == 'PENDING_PAYMENT' || status == 'PAID_WAITING_DRIVER';
  bool get needsPayment => status == 'PENDING_PAYMENT';
}

class BookingPage extends StatefulWidget {
  const BookingPage({super.key});

  @override
  State<BookingPage> createState() => _BookingPageState();
}

class _BookingPageState extends State<BookingPage> {
  // Which language toggle is active.
  bool _isEnglish = true;

  // Real bookings loaded from the server.
  List<Booking> _bookings = [];
  bool _isLoading = true;
  String? _loadError;
  final Set<int> _busyIds = {}; // bookings with a pay/cancel request running

  static const Map<String, String> _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  @override
  void initState() {
    super.initState();
    _loadBookings();
  }

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

  Future<void> _loadBookings({bool showSpinner = true}) async {
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
        Uri.parse('${ApiConfig.baseUrl}/mobile/customers/$userId/bookings'),
        headers: _headers,
      );
      if (!mounted) return;

      if (response.statusCode == 200) {
        final list = jsonDecode(response.body) as List;
        setState(() {
          _bookings = list
              .map((j) => Booking.fromJson(Map<String, dynamic>.from(j)))
              .toList();
          _isLoading = false;
          _loadError = null;
        });
      } else {
        setState(() {
          _isLoading = false;
          _loadError = _messageFrom(response, 'Could not load your bookings');
        });
      }
    } catch (e) {
      debugPrint('LOAD BOOKINGS ERROR: $e');
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _loadError = 'Unable to connect to the server.';
      });
    }
  }

  /// Pays a booking that was created but not paid yet (mock payment).
  Future<void> _handlePayBooking(Booking booking) async {
    final userId = AppSession.userId;
    if (userId == null) {
      _showMessage('Please log in again.');
      return;
    }

    setState(() => _busyIds.add(booking.id));
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/mobile/customers/$userId/bookings/${booking.id}/pay'),
        headers: _headers,
      );
      debugPrint('PAY: ${response.statusCode} ${response.body}');
      if (!mounted) return;

      if (response.statusCode == 200) {
        _showMessage('Payment successful. We are finding a driver for ${booking.orderId}.');
      } else {
        _showMessage(_messageFrom(response, 'Payment failed'));
      }
    } catch (e) {
      debugPrint('PAY ERROR: $e');
      if (mounted) _showMessage('Unable to connect to the server.');
    } finally {
      if (mounted) setState(() => _busyIds.remove(booking.id));
    }
    if (mounted) _loadBookings(showSpinner: false);
  }

  Future<void> _cancelBooking(Booking booking) async {
    final userId = AppSession.userId;
    if (userId == null) {
      _showMessage('Please log in again.');
      return;
    }

    final wasPaid = booking.status == 'PAID_WAITING_DRIVER';
    setState(() => _busyIds.add(booking.id));
    try {
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/mobile/customers/$userId/bookings/${booking.id}/cancel'),
        headers: _headers,
      );
      debugPrint('CANCEL: ${response.statusCode} ${response.body}');
      if (!mounted) return;

      if (response.statusCode == 200) {
        _showMessage(wasPaid
            ? 'Booking cancelled. Your payment was refunded to your wallet.'
            : 'Booking cancelled.');
      } else {
        _showMessage(_messageFrom(response, 'Could not cancel the booking'));
      }
    } catch (e) {
      debugPrint('CANCEL ERROR: $e');
      if (mounted) _showMessage('Unable to connect to the server.');
    } finally {
      if (mounted) setState(() => _busyIds.remove(booking.id));
    }
    if (mounted) _loadBookings(showSpinner: false);
  }

  void _handleOpenMap(Booking booking) {
    // TODO: launch maps with the booking's address / coordinates.
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Opening map for ${booking.orderId}')),
    );
  }

  void _handleCancelBooking(Booking booking) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Booking'),
        content: Text('Are you sure you want to cancel ${booking.orderId}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('No'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _cancelBooking(booking);
            },
            child: const Text('Yes, cancel'),
          ),
        ],
      ),
    );
  }

  void _handleTimelineMessages(Booking booking) {
    // TODO: navigate to a timeline / messages screen for this booking.
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Opening timeline for ${booking.orderId}')),
    );
  }

  // NOTE: This widget is shown as a tab body inside HomePage's Scaffold,
  // so it deliberately does NOT have its own Scaffold, AppBar, or
  // bottomNavigationBar - HomePage already provides those. Adding them
  // here caused the double bottom nav bar.
  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            const SizedBox(height: 16),
            Expanded(
              child: RefreshIndicator(
                onRefresh: () => _loadBookings(showSpinner: false),
                child: _buildBookingList(),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBookingList() {
    const scrollPhysics = AlwaysScrollableScrollPhysics();

    if (_isLoading) {
      return ListView(
        physics: scrollPhysics,
        children: const [
          SizedBox(height: 120),
          Center(child: CircularProgressIndicator()),
        ],
      );
    }

    if (_loadError != null) {
      return ListView(
        physics: scrollPhysics,
        children: [
          const SizedBox(height: 100),
          Center(child: Text(_loadError!, textAlign: TextAlign.center)),
          const SizedBox(height: 12),
          Center(
            child: TextButton(
              onPressed: _loadBookings,
              child: const Text('Try again'),
            ),
          ),
        ],
      );
    }

    if (_bookings.isEmpty) {
      return ListView(
        physics: scrollPhysics,
        children: const [
          SizedBox(height: 120),
          Center(child: Text('No bookings yet')),
        ],
      );
    }

    return ListView(
      physics: scrollPhysics,
      children: [
        for (final booking in _bookings) ...[
          _buildBookingCard(booking),
          const SizedBox(height: 16),
        ],
      ],
    );
  }

  Widget _buildHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        const Text(
          'Booking history',
          style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
        ),
        _buildLanguageToggle(),
      ],
    );
  }

  Widget _buildLanguageToggle() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.grey.shade200,
        borderRadius: BorderRadius.circular(20),
      ),
      padding: const EdgeInsets.all(3),
      child: Row(
        children: [
          _langChip('EN', _isEnglish),
          _langChip('BM', !_isEnglish),
        ],
      ),
    );
  }

  Widget _langChip(String label, bool active) {
    return GestureDetector(
      onTap: () => setState(() => _isEnglish = label == 'EN'),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: active ? const Color(0xFF1E2A5A) : Colors.transparent,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: active ? Colors.white : Colors.black54,
            fontWeight: FontWeight.bold,
            fontSize: 12,
          ),
        ),
      ),
    );
  }

  Widget _buildBookingCard(Booking booking) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            booking.orderId,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
          const SizedBox(height: 12),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CircleAvatar(
                radius: 20,
                backgroundColor: Colors.blue.shade100,
                child: Icon(Icons.local_laundry_service,
                    color: Colors.blue.shade700, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      booking.serviceName,
                      style: const TextStyle(
                          fontWeight: FontWeight.bold, fontSize: 16),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'RM ${booking.price.toStringAsFixed(2)}',
                      style: const TextStyle(fontSize: 15),
                    ),
                  ],
                ),
              ),
              _buildStatusBadge(booking),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            booking.address,
            style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
          ),
          const SizedBox(height: 16),
          if (booking.needsPayment) ...[
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _busyIds.contains(booking.id) ? null : () => _handlePayBooking(booking),
                icon: _busyIds.contains(booking.id)
                    ? const SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                )
                    : const Icon(Icons.payments_outlined, size: 20),
                label: Text(
                  'Pay now (RM ${booking.price.toStringAsFixed(2)})',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1E2A5A),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                ),
              ),
            ),
            const SizedBox(height: 10),
          ],
          _buildActionButton(
            icon: Icons.map_outlined,
            label: 'Open Map',
            onTap: () => _handleOpenMap(booking),
          ),
          if (booking.canCancel) ...[
            const SizedBox(height: 10),
            _buildActionButton(
              icon: Icons.cancel_outlined,
              label: 'Cancel Booking',
              onTap: _busyIds.contains(booking.id) ? null : () => _handleCancelBooking(booking),
            ),
          ],
          const SizedBox(height: 10),
          _buildActionButton(
            icon: Icons.chat_bubble_outline,
            label: 'Timeline / Messages',
            onTap: () => _handleTimelineMessages(booking),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(Booking booking) {
    // Colors follow the booking's progress.
    final MaterialColor color;
    switch (booking.status) {
      case 'PENDING_PAYMENT':
        color = Colors.orange;
        break;
      case 'PAID_WAITING_DRIVER':
        color = Colors.blue;
        break;
      case 'CANCELLED':
        color = Colors.red;
        break;
      case 'COMPLETED':
        color = Colors.green;
        break;
      default:
        color = Colors.teal; // accepted / picked up / washing / etc.
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: color.shade100,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        booking.statusLabel,
        style: TextStyle(
          color: color.shade800,
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback? onTap,
  }) {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: onTap,
        icon: Icon(icon, size: 20),
        label: Text(label, style: const TextStyle(fontWeight: FontWeight.bold)),
        style: OutlinedButton.styleFrom(
          foregroundColor: Colors.black87,
          side: BorderSide(color: Colors.grey.shade400),
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(30),
          ),
        ),
      ),
    );
  }
}