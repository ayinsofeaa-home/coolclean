import 'package:flutter/material.dart';

/// Job details page.
/// [job] is the booking JSON from the server.
/// [justAccepted] is true right after the driver taps Accept (banner says
/// "Job accepted"); when opening an older job it shows the job's status instead.
class AcceptedJobPage extends StatelessWidget {
  final Map<String, dynamic> job;
  final bool justAccepted;

  const AcceptedJobPage({super.key, required this.job, this.justAccepted = true});

  String _statusLabel(dynamic status) {
    final text = (status ?? '').toString().replaceAll('_', ' ').toLowerCase();
    if (text.isEmpty) return '-';
    return text[0].toUpperCase() + text.substring(1);
  }

  String _formatDateTime(dynamic value) {
    final parsed = DateTime.tryParse((value ?? '').toString());
    if (parsed == null) return '-';
    final d = parsed.toLocal();
    String two(int n) => n.toString().padLeft(2, '0');
    return '${two(d.day)}/${two(d.month)}/${d.year}  ${two(d.hour)}:${two(d.minute)}';
  }

  String _money(dynamic value) {
    final number = (value as num?) ?? 0;
    return 'RM ${number.toStringAsFixed(2)}';
  }

  String _address() {
    final parts = [
      job['pickupAddress'],
      job['pickupPostcode'],
      job['pickupCity'],
      job['pickupState'],
    ].where((p) => p != null && p.toString().trim().isNotEmpty).map((p) => p.toString()).toList();
    return parts.isEmpty ? '-' : parts.join(', ');
  }

  Widget _row(String label, String value, {bool bold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 110,
            child: Text(label, style: TextStyle(fontSize: 13, color: Colors.grey[600])),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontSize: 13,
                fontWeight: bold ? FontWeight.bold : FontWeight.w500,
                color: Colors.black87,
              ),
            ),
          ),
        ],
      ),
    );
  }

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
          'Job Details',
          style: TextStyle(
            color: Colors.lightBlue[900],
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Success banner
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.teal[50],
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  const Icon(Icons.check_circle, color: Colors.teal, size: 32),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          justAccepted ? 'Job accepted' : _statusLabel(job['status']),
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.lightBlue[900],
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          (job['bookingNo'] ?? '').toString(),
                          style: TextStyle(fontSize: 13, color: Colors.grey[700]),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            Text(
              'Pickup details',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: Colors.lightBlue[900],
              ),
            ),
            const SizedBox(height: 4),
            _row('Status', _statusLabel(job['status'])),
            _row('Customer', (job['customerName'] ?? '-').toString()),
            _row('Services', (job['serviceSummary'] ?? '-').toString()),
            _row('Pickup address', _address()),
            _row('Pickup time', _formatDateTime(job['pickupDateTime'])),
            const Divider(height: 28),

            Text(
              'Payment',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: Colors.lightBlue[900],
              ),
            ),
            const SizedBox(height: 4),
            _row('Laundry fee', _money(job['laundryFee'])),
            _row('Service fee', _money(job['serviceFee'])),
            _row('Delivery fee', _money(job['deliveryFee'])),
            _row('Total', _money(job['totalAmount']), bold: true),
            const SizedBox(height: 24),

            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(context),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.lightBlue[900],
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text(
                  'Back to home',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}