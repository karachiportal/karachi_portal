import 'package:flutter/material.dart';
import '../services/complaint_service.dart';
import '../widgets/shared_widgets.dart';

class TrackComplaintPage extends StatefulWidget {
  const TrackComplaintPage({super.key});
  @override
  State<TrackComplaintPage> createState() => _TrackComplaintPageState();
}

class _TrackComplaintPageState extends State<TrackComplaintPage> {
  final _controller = TextEditingController();
  bool _loading = false;
  Map<String, dynamic>? _complaint;
  String? _error;

  Future<void> _track() async {
    final number = _controller.text.trim();
    if (number.isEmpty) return;
    setState(() { _loading = true; _complaint = null; _error = null; });
    final res = await ComplaintService.trackComplaint(number);
    if (!mounted) return;
    setState(() { _loading = false; });
    if (res['success'] == true && res['data'] != null) {
      setState(() => _complaint = Map<String, dynamic>.from(res['data']));
    } else {
      setState(() => _error = 'Complaint not found. Please check the number.');
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kLightGreenBg,
      appBar: AppBar(
        backgroundColor: kPrimaryGreen,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Column(
          children: [
            Text('Track Complaint',
                style: TextStyle(
                    color: Colors.white, fontSize: 15, fontWeight: FontWeight.bold)),
            Text('شکایت ٹریک کریں',
                style: TextStyle(
                    color: Colors.white70, fontSize: 11, fontFamily: 'NotoNastaliqUrdu')),
          ],
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            _buildSearchCard(),
            SizedBox(height: 16),
            if (_loading)
              const Padding(
                padding: EdgeInsets.only(top: 40),
                child: CircularProgressIndicator(color: kPrimaryGreen),
              ),
            if (_error != null) _buildErrorCard(),
            if (_complaint != null) _buildResultCard(),
          ],
        ),
      ),
    );
  }

  Widget _buildSearchCard() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            Text('Enter Complaint Number',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: kPrimaryGreen)),
            Text('شکایت نمبر درج کریں',
                style: TextStyle(fontSize: 12, color: Colors.grey, fontFamily: 'NotoNastaliqUrdu')),
            SizedBox(height: 14),
            TextField(
              controller: _controller,
              decoration: InputDecoration(
                hintText: 'e.g. CMPL-2026-00001',
                hintStyle: TextStyle(fontSize: 13, color: Colors.grey.shade400),
                prefixIcon: Icon(Icons.search, color: kPrimaryGreen),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: kPrimaryGreen, width: 1.5),
                ),
                contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 14),
              ),
              style: TextStyle(fontSize: 13),
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => _track(),
            ),
            SizedBox(height: 14),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _loading ? null : _track,
                icon: Icon(Icons.search, color: Colors.white, size: 18),
                label: Text('Track / ٹریک کریں',
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPrimaryGreen,
                  padding: EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorCard() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(20),
        child: Column(
          children: [
            Icon(Icons.error_outline, size: 48, color: Colors.orange.shade300),
            SizedBox(height: 12),
            Text('Complaint Not Found',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.red.shade600)),
            SizedBox(height: 6),
            Text(_error!,
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 12.5, color: Colors.grey)),
          ],
        ),
      ),
    );
  }

  Widget _buildResultCard() {
    final c = _complaint!;
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: kPrimaryGreen.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(c['complaint_number'] ?? '',
                      style: TextStyle(fontSize: 12, color: kPrimaryGreen, fontWeight: FontWeight.w600)),
                ),
                Spacer(),
                StatusBadge(status: c['status'] ?? 'pending'),
              ],
            ),
            SizedBox(height: 14),
            _row('Name / نام', c['full_name'] ?? ''),
            _row('CNIC / شناخت کارڈ', c['cnic'] ?? ''),
            _row('Mobile / موبائل', c['mobile'] ?? ''),
            _row('Item / شے', c['item_name'] ?? ''),
            _row('Shop / دکان', c['shop_name'] ?? ''),
            _row('Location / مقام', c['location_address'] ?? ''),
            if (c['details'] != null && c['details'].toString().isNotEmpty) ...[
              SizedBox(height: 10),
              Text('Description / تفصیل',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: kPrimaryGreen)),
              SizedBox(height: 4),
              Text(c['details'],
                  style: TextStyle(fontSize: 12.5, color: Color(0xFF444444), height: 1.4)),
            ],
          ],
        ),
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 5),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label, style: TextStyle(fontSize: 12, color: Colors.grey)),
          ),
          Expanded(
            child: Text(value,
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: Color(0xFF1A1A1A))),
          ),
        ],
      ),
    );
  }
}
