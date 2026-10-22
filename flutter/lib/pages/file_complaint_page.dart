import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import '../widgets/shared_widgets.dart';
import '../services/complaint_service.dart';
import '../services/auth_service.dart';

class FileComplaintPage extends StatefulWidget {
  const FileComplaintPage({super.key});
  @override
  State<FileComplaintPage> createState() => _FileComplaintPageState();
}

class _FileComplaintPageState extends State<FileComplaintPage> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _cnicCtrl = TextEditingController();
  final _mobileCtrl = TextEditingController();
  final _itemCtrl = TextEditingController();
  final _shopCtrl = TextEditingController();
  final _detailsCtrl = TextEditingController();

  final List<File> _pictures = [];
  double? _latitude;
  double? _longitude;
  String? _locationAddress;
  bool _locationCaptured = false;
  bool _submitting = false;
  int _detailsLength = 0;

  @override
  void initState() {
    super.initState();
    _prefillUser();
  }

  Future<void> _prefillUser() async {
    final user = await AuthService.getCurrentUser();
    if (user != null && mounted) {
      setState(() {
        _nameCtrl.text = user['name'] ?? '';
        _cnicCtrl.text = user['cnic'] ?? '';
        _mobileCtrl.text = user['mobile'] ?? '';
      });
    }
  }

  Future<void> _captureLocation() async {
    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _showSnack('Location services are disabled / لوکیشن سروس بند ہے');
        return;
      }
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          _showSnack('Location permission denied / اجازت نہیں دی گئی');
          return;
        }
      }
      _showSnack('Getting location... / لوکیشن حاصل ہو رہی ہے...');
      Position? position;
      try {
        position = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 10),
        );
      } catch (_) {
        position = await Geolocator.getLastKnownPosition();
      }
      if (position == null) {
        _showSnack('Could not get location / لوکیشن حاصل نہیں ہو سکی');
        return;
      }
      final lat = position.latitude;
      final lng = position.longitude;
      final placemarks =
          await placemarkFromCoordinates(lat, lng);
      final place = placemarks.isNotEmpty ? placemarks.first : null;
      final address = place != null
          ? '${place.subLocality ?? ''}, ${place.locality ?? ''}, ${place.administrativeArea ?? ''}'
          : '${lat.toStringAsFixed(4)}, ${lng.toStringAsFixed(4)}';
      if (mounted) {
        setState(() {
          _latitude = lat;
          _longitude = lng;
          _locationAddress = address;
          _locationCaptured = true;
        });
      }
    } catch (e) {
      _showSnack('Could not get location: $e');
    }
  }

  Future<void> _pickImages() async {
    if (_pictures.length >= 5) {
      _showSnack('Maximum 5 pictures allowed / زیادہ سے زیادہ 5 تصاویر');
      return;
    }
    final picker = ImagePicker();
    final result = await picker.pickMultiImage(imageQuality: 70);
    if (result.isNotEmpty && mounted) {
      final remaining = 5 - _pictures.length;
      setState(() {
        _pictures.addAll(result.take(remaining).map((x) => File(x.path)));
      });
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (!_locationCaptured) {
      _showSnack('Please capture your location first / پہلے لوکیشن حاصل کریں');
      return;
    }
    if (_pictures.isEmpty) {
      _showSnack(
          'Please upload at least one picture / کم از کم ایک تصویر اپلوڈ کریں');
      return;
    }

    setState(() => _submitting = true);
    final res = await ComplaintService.fileComplaint(
      fullName: _nameCtrl.text.trim(),
      cnic: _cnicCtrl.text.trim(),
      mobile: _mobileCtrl.text.trim(),
      itemName: _itemCtrl.text.trim(),
      shopName: _shopCtrl.text.trim(),
      latitude: _latitude!,
      longitude: _longitude!,
      locationAddress: _locationAddress!,
      details: _detailsCtrl.text.trim(),
      pictures: _pictures,
    );
    setState(() => _submitting = false);

    if (res['success'] == true) {
      _showSuccessDialog(res['data']?['complaint_number'] ?? '');
    } else {
      _showSnack(res['message'] ?? 'Submission failed / جمع کرانے میں ناکامی');
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  void _showSuccessDialog(String complaintNo) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: EdgeInsets.all(16),
              decoration: const BoxDecoration(
                  color: Color(0xFFE8F5E9), shape: BoxShape.circle),
              child:
                  Icon(Icons.check_circle, color: kPrimaryGreen, size: 48),
            ),
            SizedBox(height: 16),
            Text('Complaint Submitted!\nشکایت جمع ہو گئی!',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            SizedBox(height: 8),
            Text('Complaint No: $complaintNo',
                style: const TextStyle(
                    color: kPrimaryGreen, fontWeight: FontWeight.w600)),
            SizedBox(height: 12),
            Text(
                'Your complaint will be reviewed and necessary action will be taken.\n\nآپ کی شکایت کا جائزہ لیا جائے گا اور ضروری کارروائی کی جائے گی۔',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12, color: Colors.grey)),
          ],
        ),
        actions: [
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () {
                Navigator.pop(context); // close dialog
                Navigator.pop(context); // go back
              },
              style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimaryGreen,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10))),
              child: const Text('OK', style: TextStyle(color: Colors.white)),
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
        backgroundColor: kPrimaryGreen,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Column(
          children: [
            Text('File a Complaint',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.bold)),
            Text('شکایت درج کریں',
                style: TextStyle(
                    color: Colors.white70,
                    fontSize: 12,
                    fontFamily: 'NotoNastaliqUrdu')),
          ],
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.headset_mic_outlined, color: Colors.white),
            onPressed: () => Navigator.pushNamed(context, '/help'),
            tooltip: 'Help / مدد',
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: EdgeInsets.all(16),
          child: Column(
            children: [
              _buildField(
                icon: Icons.person_outline,
                label: 'Full Name / مکمل نام',
                controller: _nameCtrl,
                hint: 'Enter your full name / اپنا مکمل نام درج کریں',
                required: true,
                validator: (v) => v!.isEmpty ? 'Name required' : null,
              ),
              _buildField(
                icon: Icons.credit_card_outlined,
                label: 'CNIC / شناختی کارڈ نمبر',
                controller: _cnicCtrl,
                hint: 'Enter your CNIC number / اپنا CNIC نمبر درج کریں',
                hint2: 'Example: 42201-1234567-1',
                required: true,
                keyboardType: TextInputType.number,
                validator: (v) {
                  if (v!.isEmpty) return 'CNIC required';
                  if (v.replaceAll('-', '').length != 13) {
                    return 'Invalid CNIC format';
                  }
                  return null;
                },
              ),
              _buildField(
                icon: Icons.phone_outlined,
                label: 'Mobile Number / موبائل نمبر',
                controller: _mobileCtrl,
                hint: 'Enter your mobile number / اپنا موبائل نمبر درج کریں',
                hint2: 'Example: 03XX-XXXXXXX',
                required: true,
                keyboardType: TextInputType.phone,
                validator: (v) {
                  if (v!.isEmpty) return 'Mobile required';
                  if (v.replaceAll('-', '').length < 10)
                    return 'Invalid number';
                  return null;
                },
              ),
              _buildField(
                icon: Icons.shopping_cart_outlined,
                label: 'Item Name / چیز کا نام',
                controller: _itemCtrl,
                hint: 'Enter item name / چیز کا نام درج کریں',
                required: true,
                isDropdown: false,
                validator: (v) => v!.isEmpty ? 'Item name required' : null,
              ),
              _buildField(
                icon: Icons.store_outlined,
                label: 'Shop Name / دکان کا نام',
                controller: _shopCtrl,
                hint: 'Enter shop name / دکان کا نام درج کریں',
                required: true,
                validator: (v) => v!.isEmpty ? 'Shop name required' : null,
              ),
              // Live Location
              _buildSectionRow(
                icon: Icons.location_on_outlined,
                label: 'Live Location / موجودہ مقام',
                required: true,
                child: Column(
                  children: [
                    GestureDetector(
                      onTap: _captureLocation,
                      child: Container(
                        width: double.infinity,
                        padding: EdgeInsets.symmetric(
                            vertical: 16, horizontal: 12),
                        decoration: BoxDecoration(
                          color: _locationCaptured
                              ? const Color(0xFFE8F5E9)
                              : kPrimaryGreen.withOpacity(0.05),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: _locationCaptured
                                ? kPrimaryGreen
                                : kPrimaryGreen.withOpacity(0.3),
                          ),
                        ),
                        child: Column(
                          children: [
                            Icon(
                              _locationCaptured
                                  ? Icons.check_circle
                                  : Icons.my_location,
                              color: kPrimaryGreen,
                              size: 22,
                            ),
                            SizedBox(height: 4),
                            Text(
                              _locationCaptured
                                  ? (_locationAddress ?? 'Location captured')
                                  : 'Capture Live Location\nموجودہ مقام حاصل کریں',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                  color: kPrimaryGreen,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w500),
                            ),
                          ],
                        ),
                      ),
                    ),
                    SizedBox(height: 8),
                    Container(
                      padding:
                          EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey.shade200),
                      ),
                      child: Row(
                        children: [
                          Icon(Icons.security,
                              size: 14, color: kPrimaryGreen),
                          SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              'Your location helps us verify your complaint.\nآپ کا مقام آپ کی شکایت کی تصدیق میں مدد کرتا ہے۔',
                              style: TextStyle(
                                  fontSize: 11, color: Colors.grey),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              // Upload Picture
              _buildSectionRow(
                icon: Icons.camera_alt_outlined,
                label: 'Upload Picture / تصویر اپلوڈ کریں',
                required: true,
                child: Column(
                  children: [
                    GestureDetector(
                      onTap: _pickImages,
                      child: Container(
                        width: double.infinity,
                        padding: EdgeInsets.symmetric(vertical: 20),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                              color: Colors.red.shade200,
                              style: BorderStyle.solid,
                              width: 1.5),
                          color: Colors.red.shade50,
                        ),
                        child: _pictures.isEmpty
                            ? Column(
                                children: [
                                  Icon(Icons.camera_alt_outlined,
                                      size: 32, color: Colors.grey),
                                  SizedBox(height: 8),
                                  Text('Tap to upload pictures',
                                      style: TextStyle(
                                          fontSize: 13,
                                          color: Color(0xFF555555))),
                                  Text('تصویر اپلوڈ کرنے کے لیے ٹیپ کریں',
                                      style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey,
                                          fontFamily: 'NotoNastaliqUrdu')),
                                  SizedBox(height: 6),
                                  Text(
                                      'You can upload up to 5 pictures / آپ زیادہ سے زیادہ 5 تصاویر اپلوڈ کر سکتے ہیں',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                          fontSize: 10.5,
                                          color: Colors.grey)),
                                ],
                              )
                            : Column(
                                children: [
                                  Wrap(
                                    spacing: 8,
                                    children: _pictures.map((f) {
                                      final idx = _pictures.indexOf(f);
                                      return Stack(
                                        children: [
                                          ClipRRect(
                                            borderRadius:
                                                BorderRadius.circular(8),
                                            child: Image.file(f,
                                                width: 70,
                                                height: 70,
                                                fit: BoxFit.cover),
                                          ),
                                          Positioned(
                                            right: 0,
                                            top: 0,
                                            child: GestureDetector(
                                              onTap: () => setState(() =>
                                                  _pictures.removeAt(idx)),
                                              child: Container(
                                                padding: EdgeInsets.all(2),
                                                decoration: const BoxDecoration(
                                                    color: Colors.red,
                                                    shape: BoxShape.circle),
                                                child: Icon(Icons.close,
                                                    size: 12,
                                                    color: Colors.white),
                                              ),
                                            ),
                                          ),
                                        ],
                                      );
                                    }).toList(),
                                  ),
                                  SizedBox(height: 8),
                                  Text('${_pictures.length}/5 pictures added',
                                      style: TextStyle(
                                          fontSize: 11, color: Colors.grey)),
                                ],
                              ),
                      ),
                    ),
                  ],
                ),
              ),
              // Complaint Details
              _buildSectionRow(
                icon: Icons.edit_note_outlined,
                label: 'Complaint Details / شکایت کی تفصیل',
                required: true,
                child: TextFormField(
                  controller: _detailsCtrl,
                  maxLines: 4,
                  maxLength: 500,
                  onChanged: (v) => setState(() => _detailsLength = v.length),
                  validator: (v) =>
                      v!.isEmpty ? 'Details required / تفصیل درج کریں' : null,
                  decoration: InputDecoration(
                    hintText:
                        'Write complaint details here...\nاپنی شکایت کی تفصیل یہاں درج کریں۔',
                    hintStyle: TextStyle(fontSize: 12.5, color: Colors.grey),
                    counterText: '$_detailsLength/500',
                    filled: true,
                    fillColor: Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide:
                          const BorderSide(color: kPrimaryGreen, width: 1.5),
                    ),
                  ),
                ),
              ),
              SizedBox(height: 20),
              // Submit Button
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton.icon(
                  onPressed: _submitting ? null : _submit,
                  icon: _submitting
                      ? SizedBox(
                          width: 18,
                          height: 18,
                          child: const CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.send, color: Colors.white),
                  label: Text(
                    _submitting
                        ? 'Submitting...'
                        : 'Submit Complaint / شکایت جمع کریں',
                    style: TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.bold),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kPrimaryGreen,
                    disabledBackgroundColor: kPrimaryGreen.withOpacity(0.5),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
              SizedBox(height: 12),
              Container(
                padding: EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: kPrimaryGreen.withOpacity(0.06),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: kPrimaryGreen.withOpacity(0.2)),
                ),
                child: Row(
                  children: [
                    Icon(Icons.security, color: kPrimaryGreen, size: 18),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Your complaint will be reviewed and necessary action will be taken.\nآپ کی شکایت کا جائزہ لیا جائے گا اور ضروری کارروائی کی جائے گی۔',
                        style: TextStyle(
                            fontSize: 11, color: Color(0xFF555555)),
                      ),
                    ),
                  ],
                ),
              ),
              SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildField({
    required IconData icon,
    required String label,
    required TextEditingController controller,
    required String hint,
    bool required = false,
    bool isDropdown = false,
    String? hint2,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return _buildSectionRow(
      icon: icon,
      label: label,
      required: required,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextFormField(
            controller: controller,
            keyboardType: keyboardType,
            validator: validator,
            style: TextStyle(fontSize: 13.5),
            decoration: InputDecoration(
              hintText: hint,
              hintStyle: TextStyle(color: Colors.grey, fontSize: 13),
              filled: true,
              fillColor: Colors.white,
              contentPadding:
                  EdgeInsets.symmetric(horizontal: 14, vertical: 14),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide(color: Colors.grey.shade300),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide(color: Colors.grey.shade300),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: kPrimaryGreen, width: 1.5),
              ),
            ),
          ),
          if (hint2 != null) ...[
            SizedBox(height: 4),
            Text(hint2, style: TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ],
      ),
    );
  }

  Widget _buildSectionRow({
    required IconData icon,
    required String label,
    required Widget child,
    bool required = false,
  }) {
    return Padding(
      padding: EdgeInsets.only(bottom: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: kPrimaryGreen.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: kPrimaryGreen, size: 20),
          ),
          SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                RichText(
                  text: TextSpan(
                    text: label,
                    style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF1A1A1A)),
                    children: required
                        ? const [
                            TextSpan(
                                text: ' *', style: TextStyle(color: Colors.red))
                          ]
                        : [],
                  ),
                ),
                SizedBox(height: 6),
                child,
              ],
            ),
          ),
        ],
      ),
    );
  }
}
