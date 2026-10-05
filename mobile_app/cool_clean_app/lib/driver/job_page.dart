import 'package:flutter/material.dart';

class JobPage extends StatefulWidget {
  const JobPage({super.key});

  @override
  State<JobPage> createState() => _JobPageState();
}

class _JobPageState extends State<JobPage> {
  final TextEditingController messageController = TextEditingController();
  String? selectedBooking = 'CC-1045';   // 🆕 default pilihan pertama
// 🆕 Data contoh untuk status timeline
  final List<Map<String, String>> timelineData = [
    {
      'status': 'Paid Waiting Driver',
      'remarks': 'Seed data for demo',
      'updatedBy': 'Admin CoolClean',
      'updatedAt': '04/05/2026 02:10',
    },
    {
      'status': 'Accepted By Driver',
      'remarks': 'Seed data for demo',
      'updatedBy': 'Admin CoolClean',
      'updatedAt': '04/05/2026 02:11',
    },
    {
      'status': 'Completed',
      'remarks': 'Job finished successfully',
      'updatedBy': 'Admin CoolClean',
      'updatedAt': '04/05/2026 03:00',
    },
  ];
  // 🆕 Data contoh untuk setiap booking
  final Map<String, Map<String, String>> bookingData = {
    'CC-1045': {
      'status': 'Completed',
      'service': 'Laundry service',
      'address': 'Wangsa Maju, Kuala Lumpur',
      'price': 'RM 31.00',
    },
    'CC-1046': {
      'status': 'In Progress',
      'service': 'Dry cleaning',
      'address': 'Bangsar, Kuala Lumpur',
      'price': 'RM 45.00',
    },
    'CC-1047': {
      'status': 'Accepted',
      'service': 'Wash & Fold',
      'address': 'Petaling Jaya, Selangor',
      'price': 'RM 28.00',
    },
  };
  @override
  Widget build(BuildContext context) {
    return Scaffold(
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
          onPressed: () {
            Navigator.pop(context);
          },
        ),
        title: Text(
          'Booking Timeline',
          style: TextStyle(
            color: Colors.lightBlue[900],
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [

          // ── Dropdown pilih booking ──
          Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 4),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.grey.withOpacity(0.15),
                blurRadius: 8,
                offset: Offset(0, 2),
              ),
            ],

          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButtonFormField<String>(
              value: selectedBooking,
              decoration: InputDecoration(
                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: InputBorder.none,
              ),
              items: const [
                DropdownMenuItem(value: 'CC-1045', child: Text('CC-1045')),
                DropdownMenuItem(value: 'CC-1046', child: Text('CC-1046')),
                DropdownMenuItem(value: 'CC-1047', child: Text('CC-1047')),
              ],
              onChanged: (value) {
                setState(() {
                  selectedBooking = value;
                  // TODO: nanti fetch data booking baru ikut value yang dipilih
                });
              },
            ),
          ),
          ),

            const SizedBox(height: 16),

            // ── Card: Booking detail ──
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16.0),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.grey.withOpacity(0.15),
                    blurRadius: 8,
                    offset: Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    selectedBooking!,
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.lightBlue[900],
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Badge status
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.teal[50],
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      bookingData[selectedBooking]!['status']!,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Colors.teal[800],
                      ),
                    ),
                  ),

                  const SizedBox(height: 16),
                  Text(
                    bookingData[selectedBooking]!['service']!,
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    bookingData[selectedBooking]!['address']!,
                    style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    bookingData[selectedBooking]!['price']!,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

// ── Status Timeline ──
            Text(
              'Status Timeline',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Colors.lightBlue[900],
              ),
            ),

            const SizedBox(height: 12),

// Loop setiap item dalam timelineData, buat satu Row untuk setiap satu
            ...timelineData.asMap().entries.map((entry) {
              int index = entry.key;
              Map<String, String> item = entry.value;
              bool isLast = index == timelineData.length - 1;

              return IntrinsicHeight(
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Bulatan + garis penyambung
                    Column(
                      children: [
                        Container(
                          width: 14,
                          height: 14,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.teal,
                          ),
                        ),
                        if (!isLast)
                          Expanded(
                            child: Container(
                              width: 2,
                              color: Colors.teal.shade100,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(width: 12),

                    // Card detail status
                    Expanded(
                      child: Padding(
                        padding: EdgeInsets.only(bottom: isLast ? 0 : 16),
                        child: Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(14.0),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.grey.withOpacity(0.15),
                                blurRadius: 6,
                                offset: Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item['status']!,
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                item['remarks']!,
                                style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${item['updatedBy']} | ${item['updatedAt']}',
                                style: TextStyle(fontSize: 11, color: Colors.grey[500]),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }).toList(),

            // 🆕 kotak "Send message" akan letak sini lepas ni

          ],
        ),
      ),
    );
  }
}