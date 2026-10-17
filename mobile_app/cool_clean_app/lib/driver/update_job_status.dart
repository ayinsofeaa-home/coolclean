import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import '../app_config.dart';

/// Bottom sheet where the driver moves a job to its NEXT step.
///
/// The server only accepts one step at a time, in this order, so this sheet
/// only ever offers the next valid step. Two steps need a proof photo
/// (clothes collected, and completed).
///
/// Closes with the updated booking JSON when the update succeeds.
class UpdateJobStatusSheet extends StatefulWidget {
  final Map<String, dynamic> job;

  const UpdateJobStatusSheet({super.key, required this.job});

  @override
  State<UpdateJobStatusSheet> createState() => _UpdateJobStatusSheetState();
}

class _UpdateJobStatusSheetState extends State<UpdateJobStatusSheet> {
  // The exact order the server accepts.
  static const List<String> _flow = [
    'ACCEPTED_BY_DRIVER',
    'DRIVER_GOING_TO_CUSTOMER',
    'CLOTHES_COLLECTED',
    'ARRIVED_AT_LAUNDRY',
    'WASHING',
    'DRYING',
    'READY_FOR_DELIVERY',
    'RETURNING_TO_CUSTOMER',
    'COMPLETED',
  ];

  // What the driver sees for each next step.
  static const Map<String, String> _stepText = {
    'DRIVER_GOING_TO_CUSTOMER': 'On my way to the customer',
    'CLOTHES_COLLECTED': 'Clothes collected',
    'ARRIVED_AT_LAUNDRY': 'Arrived at the laundry',
    'WASHING': 'Washing started',
    'DRYING': 'Drying started',
    'READY_FOR_DELIVERY': 'Laundry is ready for delivery',
    'RETURNING_TO_CUSTOMER': 'Returning to the customer',
    'COMPLETED': 'Delivered - complete the job',
  };

  // Steps that need a proof photo.
  static const Map<String, String> _proofText = {
    'CLOTHES_COLLECTED': 'Add a photo of the clothes you collected (pickup proof).',
    'COMPLETED': 'Add a photo of the delivered laundry (delivery proof).',
  };

  static const Map<String, String> _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  final ImagePicker _picker = ImagePicker();
  XFile? _photo;
  Uint8List? _photoBytes;
  bool _isSubmitting = false;
  String? _error;

  String get _current => (widget.job['status'] ?? '').toString();

  /// The only status the server will accept next (null = nothing left to do).
  String? get _next {
    final index = _flow.indexOf(_current);
    if (index < 0 || index >= _flow.length - 1) return null;
    return _flow[index + 1];
  }

  String _label(String status) {
    final text = status.replaceAll('_', ' ').toLowerCase();
    if (text.isEmpty) return '';
    return text[0].toUpperCase() + text.substring(1);
  }

  /// Reads the server's error message (a crashed server returns HTML, not JSON).
  String _messageFrom(http.Response response, String fallback) {
    try {
      final data = jsonDecode(response.body);
      if (data is Map && data['message'] != null) return data['message'].toString();
    } catch (_) {}
    return '$fallback (server error ${response.statusCode}).';
  }

  Future<void> _pickPhoto(ImageSource source) async {
    try {
      // Resized + compressed so it fits within the server's size limit.
      final file = await _picker.pickImage(
        source: source,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 60,
      );
      if (file == null) return;
      final bytes = await file.readAsBytes();
      if (!mounted) return;
      setState(() {
        _photo = file;
        _photoBytes = bytes;
        _error = null;
      });
    } catch (e) {
      debugPrint('PICK PHOTO ERROR: $e');
      if (mounted) setState(() => _error = 'Could not open the photo. Please try again.');
    }
  }

  Future<void> _submit() async {
    final next = _next;
    final userId = AppSession.userId;
    if (next == null) return;
    if (userId == null) {
      setState(() => _error = 'Please log in again.');
      return;
    }

    String? proofImage;
    if (_proofText.containsKey(next)) {
      if (_photoBytes == null) {
        setState(() => _error = 'Please add the proof photo first.');
        return;
      }
      final isPng = (_photo?.path ?? '').toLowerCase().endsWith('.png');
      proofImage = 'data:image/${isPng ? 'png' : 'jpeg'};base64,${base64Encode(_photoBytes!)}';
      if (proofImage.length > 1400000) {
        setState(() => _error = 'That photo is too large. Please take a smaller one.');
        return;
      }
    }

    setState(() {
      _isSubmitting = true;
      _error = null;
    });

    try {
      final jobId = (widget.job['id'] as num).toInt();
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/mobile/drivers/$userId/jobs/$jobId/status'),
        headers: _headers,
        body: jsonEncode({
          'status': next,
          if (proofImage != null) 'proofImage': proofImage,
        }),
      );
      final shown = response.body.length > 300 ? response.body.substring(0, 300) : response.body;
      debugPrint('UPDATE STATUS: ${response.statusCode} $shown');
      if (!mounted) return;

      if (response.statusCode == 200) {
        Navigator.pop(context, Map<String, dynamic>.from(jsonDecode(response.body)));
        return;
      }
      setState(() => _error = _messageFrom(response, 'Could not update the status'));
    } catch (e) {
      debugPrint('UPDATE STATUS ERROR: $e');
      if (mounted) setState(() => _error = 'Unable to connect to the server.');
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
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
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final next = _next;

    return Padding(
      // Keeps the sheet above the keyboard / system bars.
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 14, 20, 28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Update job status',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.lightBlue[900],
              ),
            ),
            const SizedBox(height: 4),
            Text(
              (widget.job['bookingNo'] ?? '').toString(),
              style: TextStyle(fontSize: 13, color: Colors.grey[600]),
            ),
            const SizedBox(height: 14),
            _infoRow('Current status', _label(_current)),
            if (next != null)
              _infoRow('Next step', _stepText[next] ?? _label(next))
            else
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Text(
                  'This job has no further progress to update.',
                  style: TextStyle(fontSize: 13, color: Colors.grey[700]),
                ),
              ),

            // Proof photo (only for the steps that need one)
            if (next != null && _proofText.containsKey(next)) ...[
              const SizedBox(height: 14),
              Text(
                _proofText[next]!,
                style: TextStyle(fontSize: 13, color: Colors.grey[800]),
              ),
              const SizedBox(height: 10),
              if (_photoBytes != null)
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.memory(
                    _photoBytes!,
                    height: 180,
                    width: double.infinity,
                    fit: BoxFit.cover,
                  ),
                ),
              if (_photoBytes != null) const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _isSubmitting ? null : () => _pickPhoto(ImageSource.camera),
                      icon: const Icon(Icons.photo_camera_outlined, size: 18),
                      label: Text(_photoBytes == null ? 'Camera' : 'Retake'),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _isSubmitting ? null : () => _pickPhoto(ImageSource.gallery),
                      icon: const Icon(Icons.photo_library_outlined, size: 18),
                      label: const Text('Gallery'),
                    ),
                  ),
                ],
              ),
            ],

            if (_error != null) ...[
              const SizedBox(height: 14),
              Text(_error!, style: TextStyle(fontSize: 13, color: Colors.red.shade700)),
            ],

            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: (next == null || _isSubmitting) ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.teal,
                  foregroundColor: Colors.white,
                  disabledBackgroundColor: Colors.grey.shade300,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _isSubmitting
                    ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                )
                    : const Text(
                  'Confirm update',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}