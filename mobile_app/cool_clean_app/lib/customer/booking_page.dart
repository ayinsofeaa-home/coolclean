import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../app_config.dart';

/// Simple coordinate holder so we don't need the google_maps_flutter
/// package yet. Swap this out for LatLng from google_maps_flutter later
/// when you're ready to add the real map.
class SimpleLatLng {
  final double latitude;
  final double longitude;
  const SimpleLatLng(this.latitude, this.longitude);
}

/// A single selectable service (Wash, Dry, Folding, Ironing, etc).
class BookingService {
  final int id; // service id on the server
  final String title;
  final String subtitle;
  final double price;
  final bool available;

  const BookingService({
    required this.id,
    required this.title,
    required this.subtitle,
    required this.price,
    this.available = true,
  });
}

class CreateBookingPage extends StatefulWidget {
  // Pre-fill from the user's saved registration address, if you have one.
  final String initialAddress;
  final String initialPostcode;
  final String initialCity;
  final String initialState;
  final SimpleLatLng initialLatLng;

  const CreateBookingPage({
    super.key,
    this.initialAddress = '',
    this.initialPostcode = '',
    this.initialCity = '',
    this.initialState = 'Selangor',
    this.initialLatLng = const SimpleLatLng(3.0296, 101.7838), // Bandar Baru Bangi area
  });

  @override
  State<CreateBookingPage> createState() => _CreateBookingPageState();
}

class _CreateBookingPageState extends State<CreateBookingPage> {
  bool _isEnglish = true;

  // ---- Services (loaded from the server) ----
  List<BookingService> _services = [];
  bool _isLoadingServices = true;
  String? _loadError;

  // Tracks which service titles are checked.
  final Set<String> _selectedServices = {};

  // Flat fees - replaced with the server's values once they load.
  double _serviceFee = 2.00;
  double _deliveryFee = 6.00;

  // True while the booking / payment request is running.
  bool _isSubmitting = false;

  static const Map<String, String> _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  double get _laundryFee =>
      _services
          .where((s) => _selectedServices.contains(s.title))
          .fold(0.0, (sum, s) => sum + s.price);

  double get _totalAmount => _laundryFee + _serviceFee + _deliveryFee;

  // ---- Address form ----
  late final TextEditingController _addressController =
  TextEditingController(text: widget.initialAddress.isNotEmpty ? widget.initialAddress : (AppSession.defaultAddress ?? ''))..addListener(_onFormChanged);
  late final TextEditingController _postcodeController =
  TextEditingController(text: widget.initialPostcode.isNotEmpty ? widget.initialPostcode : (AppSession.defaultPostcode ?? ''))..addListener(_onFormChanged);
  late final TextEditingController _cityController =
  TextEditingController(text: widget.initialCity.isNotEmpty ? widget.initialCity : (AppSession.defaultCity ?? ''))..addListener(_onFormChanged);
  late String _selectedState = _malaysianStates.contains(AppSession.defaultState)
      ? AppSession.defaultState!
      : widget.initialState;

  final List<String> _malaysianStates = const [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang',
    'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor',
    'Terengganu', 'Kuala Lumpur', 'Labuan', 'Putrajaya',
  ];

  // ---- Map (placeholder for now) ----
  late final SimpleLatLng _pinLocation = widget.initialLatLng;
  bool _isValidatingAddress = false;
  bool _addressValidated = false;

  // ---- Form validation ----
  void _onFormChanged() => setState(() {});


  // Set to true only after the user taps Confirm Booking with something
  // missing — controls whether the red error boxes/text are shown.
  bool _showServicesError = false;
  bool _showAddressError = false;
  bool _showPostcodeError = false;
  bool _showCityError = false;

  @override
  void initState() {
    super.initState();
    _loadServicesAndPricing();
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

  /// Fetches the real services and fees (service fee + delivery fee).
  Future<void> _loadServicesAndPricing() async {
    setState(() {
      _isLoadingServices = true;
      _loadError = null;
    });

    try {
      final results = await Future.wait([
        http.get(Uri.parse('${ApiConfig.baseUrl}/mobile/services'), headers: _headers),
        http.get(Uri.parse('${ApiConfig.baseUrl}/mobile/pricing'), headers: _headers),
      ]);
      if (!mounted) return;

      if (results[0].statusCode != 200) {
        throw Exception('services returned ${results[0].statusCode}');
      }

      final list = jsonDecode(results[0].body) as List;
      setState(() {
        _services = list.map<BookingService>((s) {
          return BookingService(
            id: (s['id'] as num).toInt(),
            title: (s['name'] ?? '').toString(),
            subtitle: (s['description'] ?? '').toString(),
            price: (s['price'] as num?)?.toDouble() ?? 0,
            available: (s['status'] ?? '').toString().toUpperCase() == 'ACTIVE',
          );
        }).toList();

        if (results[1].statusCode == 200) {
          final pricing = jsonDecode(results[1].body);
          _serviceFee = (pricing['serviceFee'] as num?)?.toDouble() ?? _serviceFee;
          _deliveryFee = (pricing['deliveryFee'] as num?)?.toDouble() ?? _deliveryFee;
        }
        _isLoadingServices = false;
      });
    } catch (e) {
      debugPrint('LOAD SERVICES ERROR: $e');
      if (!mounted) return;
      setState(() {
        _isLoadingServices = false;
        _loadError = 'Could not load the services. Check your connection and try again.';
      });
    }
  }

  @override
  void dispose() {
    _addressController.removeListener(_onFormChanged);
    _postcodeController.removeListener(_onFormChanged);
    _cityController.removeListener(_onFormChanged);
    _addressController.dispose();
    _postcodeController.dispose();
    _cityController.dispose();
    super.dispose();
  }

  void _toggleService(BookingService service) {
    if (!service.available) return;
    setState(() {
      if (_selectedServices.contains(service.title)) {
        _selectedServices.remove(service.title);
      } else {
        _selectedServices.add(service.title);
      }
      if (_selectedServices.isNotEmpty) _showServicesError = false;
    });
  }

  Future<void> _handleValidateAddress() async {
    if (_addressController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a pickup address first')),
      );
      return;
    }

    setState(() => _isValidatingAddress = true);

    // TODO: once you add a real map/geocoder, replace this with a real
    // geocoding call built from _addressController.text +
    // _postcodeController.text + _cityController.text + _selectedState,
    // then update _pinLocation with the coordinates it returns.
    await Future.delayed(const Duration(milliseconds: 600));

    if (!mounted) return;
    setState(() {
      _isValidatingAddress = false;
      _addressValidated = true;
    });
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Address validated (map preview coming soon)')),
    );
  }

  Future<void> _handleConfirmBooking() async {
    final bool servicesMissing = _selectedServices.isEmpty;
    final bool addressMissing = _addressController.text.trim().isEmpty;
    final bool postcodeMissing = _postcodeController.text.trim().isEmpty;
    final bool cityMissing = _cityController.text.trim().isEmpty;

    setState(() {
      _showServicesError = servicesMissing;
      _showAddressError = addressMissing;
      _showPostcodeError = postcodeMissing;
      _showCityError = cityMissing;
    });

    if (servicesMissing || addressMissing || postcodeMissing || cityMissing) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in the fields highlighted in red')),
      );
      return;
    }

    final userId = AppSession.userId;
    if (userId == null) {
      _showMessage('Please log in again.');
      return;
    }

    final serviceIds = _services
        .where((s) => _selectedServices.contains(s.title))
        .map((s) => s.id)
        .toList();

    setState(() => _isSubmitting = true);
    Map<String, dynamic>? createdBooking;

    try {
      // Step 1: create the booking (status starts as PENDING_PAYMENT).
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/mobile/customers/$userId/bookings'),
        headers: _headers,
        body: jsonEncode({
          'serviceIds': serviceIds,
          'pickupAddress': _addressController.text.trim(),
          'pickupPostcode': _postcodeController.text.trim(),
          'pickupCity': _cityController.text.trim(),
          'pickupState': _selectedState,
        }),
      );
      debugPrint('CREATE BOOKING: ${response.statusCode} ${response.body}');
      if (!mounted) return;

      if (response.statusCode == 200) {
        createdBooking = Map<String, dynamic>.from(jsonDecode(response.body));
      } else {
        _showMessage(_messageFrom(response, 'Could not create the booking'));
      }
    } catch (e) {
      debugPrint('CREATE BOOKING ERROR: $e');
      if (mounted) _showMessage('Unable to connect to the server.');
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }

    if (createdBooking != null && mounted) {
      await _askToPay(userId, createdBooking);
    }
  }

  /// After the booking exists, ask whether to pay now. Drivers only see a
  /// booking once it is paid.
  Future<void> _askToPay(int userId, Map<String, dynamic> booking) async {
    final total = (booking['totalAmount'] as num?) ?? _totalAmount;
    final bookingNo = (booking['bookingNo'] ?? '').toString();

    final payNow = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Booking created'),
        content: Text(
          '$bookingNo\nTotal: RM ${total.toStringAsFixed(2)}\n\n'
              'Pay now so drivers can accept your booking.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Pay later'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Pay now'),
          ),
        ],
      ),
    );
    if (!mounted) return;

    if (payNow != true) {
      _showMessage('Booking saved. Open the Bookings tab and tap Pay now to send it to drivers.');
      Navigator.pop(context);
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      // Step 2: pay (mock payment; the wallet is used first if it has enough).
      final bookingId = (booking['id'] as num).toInt();
      final response = await http.post(
        Uri.parse('${ApiConfig.baseUrl}/mobile/customers/$userId/bookings/$bookingId/pay'),
        headers: _headers,
      );
      debugPrint('PAY BOOKING: ${response.statusCode} ${response.body}');
      if (!mounted) return;

      if (response.statusCode == 200) {
        _showMessage('Payment successful. We are finding a driver for you.');
      } else {
        _showMessage(
          '${_messageFrom(response, 'Payment failed')} You can pay later from the Bookings tab.',
        );
      }
    } catch (e) {
      debugPrint('PAY BOOKING ERROR: $e');
      if (mounted) _showMessage('Unable to connect to the server. You can pay later from the Bookings tab.');
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }

    if (mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        foregroundColor: Colors.black,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Create booking',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 20),
        ),
        actions: [_buildLanguageToggle(), const SizedBox(width: 12)],
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            _buildSelectServicesSection(),
            const SizedBox(height: 20),
            _buildAmountToPayCard(),
            const SizedBox(height: 24),
            _buildPickupAddressSection(),
            const SizedBox(height: 16),
            _buildValidateAddressButton(),
            const SizedBox(height: 12),
            _buildHintBanner(
              'Enter the pickup address, then tap Validate address to pin the location automatically.',
            ),
            const SizedBox(height: 16),
            _buildMapPlaceholder(),
            const SizedBox(height: 8),
            Text(
              'Map preview will appear here once Google Maps is connected.',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 16),
            _buildHintBanner(
              'The pickup address is prefilled from your registration. Update it here only when this booking uses a different location.',
            ),
            const SizedBox(height: 20),
            _buildConfirmButton(),
            const SizedBox(height: 20),
          ],
        ),
      ),
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

  Widget _buildSelectServicesSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Select services',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        if (_isLoadingServices)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 24),
            child: Center(child: CircularProgressIndicator()),
          )
        else if (_loadError != null)
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(_loadError!, style: TextStyle(color: Colors.red.shade400)),
              TextButton(
                onPressed: _loadServicesAndPricing,
                child: const Text('Try again'),
              ),
            ],
          )
        else
          for (final service in _services) ...[
            _buildServiceRow(service),
            const SizedBox(height: 12),
          ],
        if (_showServicesError)
          Padding(
            padding: const EdgeInsets.only(top: 4, left: 4),
            child: Text(
              'Please select at least one service',
              style: TextStyle(fontSize: 12, color: Colors.red.shade400),
            ),
          ),
      ],
    );
  }

  Widget _buildServiceRow(BookingService service) {
    final bool isChecked = _selectedServices.contains(service.title);
    final bool showError = _showServicesError && _selectedServices.isEmpty;

    return GestureDetector(
      onTap: () => _toggleService(service),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: service.available ? Colors.grey.shade50 : Colors.grey.shade100,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: showError
                ? Colors.red.shade400
                : (isChecked ? Colors.blue.shade400 : Colors.grey.shade300),
            width: showError || isChecked ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Checkbox(
              value: isChecked,
              onChanged: service.available ? (_) => _toggleService(service) : null,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    service.title,
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                      color: service.available ? Colors.black87 : Colors.grey,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    service.available ? service.subtitle : 'Currently unavailable',
                    style: TextStyle(
                      fontSize: 13,
                      color: service.available ? Colors.grey.shade700 : Colors.grey,
                    ),
                  ),
                  if (service.available) ...[
                    const SizedBox(height: 2),
                    Text(
                      'RM ${service.price.toStringAsFixed(2)}',
                      style: const TextStyle(fontSize: 13, color: Colors.black54),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAmountToPayCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: Colors.blue.shade50,
                child: Icon(Icons.payments_outlined, color: Colors.blue.shade700, size: 20),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Text('Amount to pay', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              ),
              Text(
                'RM ${_totalAmount.toStringAsFixed(2)}',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Colors.green),
              ),
            ],
          ),
          const SizedBox(height: 16),
          _buildFeeRow('Laundry Fee', _laundryFee),
          const SizedBox(height: 6),
          _buildFeeRow('Service Fee', _serviceFee),
          const SizedBox(height: 6),
          _buildFeeRow('Delivery Fee', _deliveryFee),
        ],
      ),
    );
  }

  Widget _buildFeeRow(String label, double amount) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: Colors.grey.shade700)),
        Text('RM ${amount.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold)),
      ],
    );
  }

  Widget _buildPickupAddressSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Pickup address',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        const SizedBox(height: 12),
        _buildLabeledField(
          label: 'Pickup address',
          controller: _addressController,
          icon: Icons.location_on_outlined,
          showError: _showAddressError,
          errorText: 'Please enter a pickup address',
          onChangedClearsError: () => setState(() => _showAddressError = false),
        ),
        const SizedBox(height: 12),
        _buildLabeledField(
          label: 'Postcode',
          controller: _postcodeController,
          icon: Icons.markunread_mailbox_outlined,
          keyboardType: TextInputType.number,
          showError: _showPostcodeError,
          errorText: 'Please enter a postcode',
          onChangedClearsError: () => setState(() => _showPostcodeError = false),
        ),
        const SizedBox(height: 12),
        _buildLabeledField(
          label: 'City',
          controller: _cityController,
          icon: Icons.location_city_outlined,
          showError: _showCityError,
          errorText: 'Please enter a city',
          onChangedClearsError: () => setState(() => _showCityError = false),
        ),
        const SizedBox(height: 12),
        _buildStateDropdown(),
      ],
    );
  }

  Widget _buildLabeledField({
    required String label,
    required TextEditingController controller,
    required IconData icon,
    TextInputType? keyboardType,
    bool showError = false,
    String? errorText,
    VoidCallback? onChangedClearsError,
  }) {
    final Color errorColor = Colors.red.shade400;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 13,
            color: showError ? errorColor : Colors.grey.shade700,
          ),
        ),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          keyboardType: keyboardType,
          onChanged: (_) => onChangedClearsError?.call(),
          style: TextStyle(color: showError ? errorColor : Colors.black87),
          decoration: InputDecoration(
            prefixIcon: Icon(icon, color: showError ? errorColor : Colors.grey.shade600),
            filled: true,
            fillColor: Colors.white,
            contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 4),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(28),
              borderSide: BorderSide(color: Colors.grey.shade300),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(28),
              borderSide: BorderSide(color: showError ? errorColor : Colors.grey.shade300, width: showError ? 1.5 : 1),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(28),
              borderSide: BorderSide(color: showError ? errorColor : Colors.blue.shade400, width: 1.5),
            ),
          ),
        ),
        if (showError && errorText != null)
          Padding(
            padding: const EdgeInsets.only(top: 6, left: 4),
            child: Text(
              errorText,
              style: TextStyle(fontSize: 12, color: errorColor),
            ),
          ),
      ],
    );
  }

  Widget _buildStateDropdown() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('State', style: TextStyle(fontSize: 13, color: Colors.grey.shade700)),
        const SizedBox(height: 6),
        DropdownButtonFormField<String>(
          initialValue: _selectedState,
          icon: const Icon(Icons.keyboard_arrow_down),
          decoration: InputDecoration(
            prefixIcon: const Icon(Icons.map_outlined),
            filled: true,
            fillColor: Colors.grey.shade50,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey.shade300),
            ),
          ),
          items: _malaysianStates
              .map((state) => DropdownMenuItem(value: state, child: Text(state)))
              .toList(),
          onChanged: (value) {
            if (value != null) setState(() => _selectedState = value);
          },
        ),
      ],
    );
  }

  Widget _buildValidateAddressButton() {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: _isValidatingAddress ? null : _handleValidateAddress,
        icon: _isValidatingAddress
            ? const SizedBox(
          width: 16,
          height: 16,
          child: CircularProgressIndicator(strokeWidth: 2),
        )
            : const Icon(Icons.verified_outlined, size: 20),
        label: Text(_isValidatingAddress ? 'Validating...' : 'Validate address'),
        style: OutlinedButton.styleFrom(
          foregroundColor: Colors.black87,
          side: BorderSide(color: Colors.grey.shade400),
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
        ),
      ),
    );
  }

  Widget _buildHintBanner(String text) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.green.shade50,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.green.shade100),
      ),
      child: Text(
        text,
        style: TextStyle(fontSize: 13, color: Colors.green.shade800),
      ),
    );
  }

  /// Simple placeholder box standing in for the real Google Map.
  /// Swap this widget's contents for a GoogleMap widget once you have
  /// google_maps_flutter set up and an API key.
  Widget _buildMapPlaceholder() {
    return Container(
      height: 220,
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.green.shade50,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.green.shade100),
      ),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              _addressValidated ? Icons.location_on : Icons.map_outlined,
              size: 40,
              color: _addressValidated ? Colors.red.shade400 : Colors.grey.shade400,
            ),
            const SizedBox(height: 8),
            Text(
              _addressValidated
                  ? 'Pinned at ${_pinLocation.latitude.toStringAsFixed(4)}, ${_pinLocation.longitude.toStringAsFixed(4)}'
                  : 'Map preview (not connected yet)',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildConfirmButton() {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _isSubmitting ? null : _handleConfirmBooking,
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF1E2A5A),
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 16),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
        ),
        child: Text(
          _isSubmitting ? 'Please wait...' : 'Confirm Booking',
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}