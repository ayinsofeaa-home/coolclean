import 'package:flutter/material.dart';

/// Data model for a single booking entry.
class Booking {
  final String orderId;
  final String serviceName;
  final double price;
  final String status;
  final String address;

  const Booking({
    required this.orderId,
    required this.serviceName,
    required this.price,
    required this.status,
    required this.address,
  });
}

class BookingPage extends StatefulWidget {
  const BookingPage({super.key});

  @override
  State<BookingPage> createState() => _BookingPageState();
}

class _BookingPageState extends State<BookingPage> {
  // Which language toggle is active.
  bool _isEnglish = true;

  // Sample data - replace this with data from your backend / API.
  final List<Booking> _bookings = const [
    Booking(
      orderId: 'CC-1785944694530',
      serviceName: 'Wash, Dry',
      price: 30.00,
      status: 'Paid Waiting Driver',
      address: 'no 16, jalan 5e/6 seksyen 5, 43650, bandar baru bangi, Selangor, Malaysia',
    ),
    Booking(
      orderId: 'CC-1785917904509',
      serviceName: 'Wash',
      price: 20.00,
      status: 'Paid Waiting Driver',
      address: 'no 16, jalan 5e/6 seksyen 5, 43650, bandar baru bangi, Selangor, Malaysia',
    ),
  ];

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
              // TODO: call your cancel-booking API here, then refresh the list.
              setState(() {
                _bookings.removeWhere((b) => b.orderId == booking.orderId);
              });
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
              child: _bookings.isEmpty
                  ? const Center(child: Text('No bookings yet'))
                  : ListView(
                children: [
                  for (final booking in _bookings) ...[
                    _buildBookingCard(booking),
                    const SizedBox(height: 16),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
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
              _buildStatusBadge(booking.status),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            booking.address,
            style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
          ),
          const SizedBox(height: 16),
          _buildActionButton(
            icon: Icons.map_outlined,
            label: 'Open Map',
            onTap: () => _handleOpenMap(booking),
          ),
          const SizedBox(height: 10),
          _buildActionButton(
            icon: Icons.cancel_outlined,
            label: 'Cancel Booking',
            onTap: () => _handleCancelBooking(booking),
          ),
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

  Widget _buildStatusBadge(String status) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.green.shade100,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        status,
        style: TextStyle(
          color: Colors.green.shade800,
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
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