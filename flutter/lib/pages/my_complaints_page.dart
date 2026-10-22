import 'package:flutter/material.dart';
import '../widgets/shared_widgets.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';
import '../widgets/offline_banner.dart';
import '../services/complaint_service.dart';
import '../services/cache_service.dart';
import 'file_complaint_page.dart';

// ════════════════════════════════════════════════════════
//  MY COMPLAINTS PAGE
// ════════════════════════════════════════════════════════
class MyComplaintsPage extends StatefulWidget {
  final bool standalone;
  const MyComplaintsPage({super.key, this.standalone = true});
  @override
  State<MyComplaintsPage> createState() => _MyComplaintsPageState();
}

class _MyComplaintsPageState extends State<MyComplaintsPage> {
  String _selectedFilter = 'All';
  List<Map<String, dynamic>> _complaints = [];
  bool _loading = true;
  bool _isOffline = false;
  int _page = 1;
  bool _hasMore = true;
  final _scrollCtrl = ScrollController();

  final List<String> _filters = ['All', 'Pending', 'In Progress', 'Resolved'];

  @override
  void initState() {
    super.initState();
    _loadComplaints();
    _scrollCtrl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollCtrl.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >=
            _scrollCtrl.position.maxScrollExtent - 200 &&
        _hasMore &&
        !_loading) {
      _loadMore();
    }
  }

  Future<void> _loadComplaints({bool reset = true}) async {
    if (reset) {
      setState(() {
        _loading = true;
        _page = 1;
        _complaints = [];
      });
    }
    final res = await ComplaintService.getMyComplaints(
      status: _selectedFilter == 'All' ? null : _selectedFilter,
      page: _page,
    );
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          final list = res['data']?['data'] ?? res['data'] ?? [];
          final newItems = List<Map<String, dynamic>>.from(list);
          if (reset) {
            _complaints = newItems;
            CacheService.save('cache_complaints', newItems);
            _isOffline = false;
          } else {
            _complaints.addAll(newItems);
          }
          final meta = res['data']?['meta'];
          _hasMore = meta != null
              ? (meta['current_page'] < meta['last_page'])
              : newItems.length >= 10;
        }
      });
    }
    if (!res['success'] && reset && mounted) {
      final cached = await CacheService.load('cache_complaints');
      if (cached != null && mounted) {
        setState(() {
          _complaints = List<Map<String, dynamic>>.from(cached);
          _isOffline = true;
          _hasMore = false;
        });
      }
    }
  }

  Future<void> _loadMore() async {
    _page++;
    await _loadComplaints(reset: false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kLightGreenBg,
      appBar: widget.standalone ? const KarachiAppBar(showBack: true) : null,
      floatingActionButton: FloatingActionButton(
        backgroundColor: kPrimaryGreen,
        child: const Icon(Icons.add, color: Colors.white),
        onPressed: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const FileComplaintPage()))
            .then((_) => _loadComplaints()),
      ),
      body: Column(
        children: [
          OfflineBanner(isOffline: _isOffline),
          _buildFilterTabs(),
          Expanded(
            child: _loading && _complaints.isEmpty
                ? const Center(
                    child: CircularProgressIndicator(color: kPrimaryGreen))
                : _complaints.isEmpty
                    ? _buildEmpty()
                    : RefreshIndicator(
                        color: kPrimaryGreen,
                        onRefresh: () => _loadComplaints(),
                        child: ListView.builder(
                          controller: _scrollCtrl,
                          padding: EdgeInsets.all(16),
                          itemCount: _complaints.length + (_hasMore ? 1 : 0),
                          itemBuilder: (_, i) {
                            if (i == _complaints.length) {
                              return Center(
                                  child: Padding(
                                      padding: EdgeInsets.all(16),
                                      child: CircularProgressIndicator(
                                          color: kPrimaryGreen)));
                            }
                            return _complaintCard(_complaints[i]);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterTabs() {
    return Container(
      color: Colors.white,
      padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Row(
        children: _filters.map((f) {
          final isSelected = _selectedFilter == f;
          return Expanded(
            child: GestureDetector(
              onTap: () {
                setState(() => _selectedFilter = f);
                _loadComplaints();
              },
              child: Container(
                margin: EdgeInsets.symmetric(horizontal: 3),
                padding: EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: isSelected ? kPrimaryGreen : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(f,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        fontSize: 11.5,
                        fontWeight:
                            isSelected ? FontWeight.bold : FontWeight.normal,
                        color:
                            isSelected ? Colors.white : Colors.grey.shade700)),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _complaintCard(Map<String, dynamic> c) {
    return GestureDetector(
      onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
              builder: (_) =>
                  ComplaintDetailPage(complaintId: c['id'].toString()))),
      child: Container(
        margin: EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 6,
                offset: const Offset(0, 2))
          ],
        ),
        child: Padding(
          padding: EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding:
                        EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: kPrimaryGreen.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(c['complaint_number'] ?? '#CMPL-${c['id']}',
                        style: TextStyle(
                            fontSize: 11,
                            color: kPrimaryGreen,
                            fontWeight: FontWeight.w600)),
                  ),
                  const Spacer(),
                  StatusBadge(status: c['status'] ?? 'pending'),
                ],
              ),
              SizedBox(height: 10),
              Text(c['item_name'] ?? 'Complaint',
                  style: TextStyle(
                      fontSize: 14.5, fontWeight: FontWeight.bold)),
              SizedBox(height: 4),
              Row(
                children: [
                  Icon(Icons.store_outlined, size: 13, color: Colors.grey),
                  SizedBox(width: 4),
                  Expanded(
                    child: Text(c['shop_name'] ?? '',
                        style: TextStyle(fontSize: 12, color: Colors.grey)),
                  ),
                ],
              ),
              SizedBox(height: 3),
              Row(
                children: [
                  Icon(Icons.location_on_outlined,
                      size: 13, color: Colors.grey),
                  SizedBox(width: 4),
                  Expanded(
                    child: Text(c['location_address'] ?? c['shop_area'] ?? '',
                        style: TextStyle(fontSize: 12, color: Colors.grey)),
                  ),
                ],
              ),
              SizedBox(height: 6),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(c['created_date'] ?? '',
                      style: TextStyle(fontSize: 11, color: Colors.grey)),
                  Icon(Icons.chevron_right, size: 18, color: Colors.grey),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.report_problem_outlined, size: 56, color: Colors.grey),
          SizedBox(height: 12),
          Text(
              _selectedFilter == 'All'
                  ? 'No complaints yet\nابھی کوئی شکایت نہیں'
                  : 'No $_selectedFilter complaints\nکوئی شکایت نہیں',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey, fontSize: 14)),
          SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: () => Navigator.push(context,
                MaterialPageRoute(builder: (_) => const FileComplaintPage())),
            icon: const Icon(Icons.add, color: Colors.white),
            label: const Text('File a Complaint',
                style: TextStyle(color: Colors.white)),
            style: ElevatedButton.styleFrom(
              backgroundColor: kPrimaryGreen,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
            ),
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════
//  COMPLAINT DETAIL PAGE
// ════════════════════════════════════════════════════════
class ComplaintDetailPage extends StatefulWidget {
  final String complaintId;
  const ComplaintDetailPage({super.key, required this.complaintId});
  @override
  State<ComplaintDetailPage> createState() => _ComplaintDetailPageState();
}

class _ComplaintDetailPageState extends State<ComplaintDetailPage> {
  Map<String, dynamic>? _complaint;
  bool _loading = true;
  bool _isOffline = false;
  final _cacheKey = 'cache_complaint_';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final res = await ComplaintService.getComplaintDetail(widget.complaintId);
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          final data = Map<String, dynamic>.from(res['data'] ?? {});
          CacheService.save('${_cacheKey}${widget.complaintId}', data);
          _complaint = data;
          _isOffline = false;
        }
      });
    }
    if (!res['success'] && mounted) {
      final cached = await CacheService.load('${_cacheKey}${widget.complaintId}');
      if (cached != null && mounted) {
        setState(() {
          _complaint = Map<String, dynamic>.from(cached);
          _isOffline = true;
        });
      }
    }
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
            Text('Complaint Details',
                style: TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.bold)),
            Text('شکایت کی تفصیلات',
                style: TextStyle(
                    color: Colors.white70,
                    fontSize: 11,
                    fontFamily: 'NotoNastaliqUrdu')),
          ],
        ),
        centerTitle: true,
      ),
      body: Column(
        children: [
          OfflineBanner(isOffline: _isOffline),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: kPrimaryGreen))
                : _complaint == null
                    ? const Center(child: Text('Complaint not found'))
                    : SingleChildScrollView(
                        padding: EdgeInsets.all(16),
                        child: Column(
                          children: [
                            _buildInfoCard(),
                            SizedBox(height: 14),
                            _buildPicturesCard(),
                            SizedBox(height: 14),
                            _buildDescriptionCard(),
                            SizedBox(height: 14),
                            _buildSubmittedCard(),
                            SizedBox(height: 14),
                            _buildTimelineCard(),
                            SizedBox(height: 20),
                          ],
                        ),
                      ),
          ),
        ],
      ));
  }

  Widget _buildInfoCard() {
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
                  child: Text(c['complaint_number'] ?? '#CMPL-${c['id']}',
                      style: TextStyle(
                          fontSize: 12,
                          color: kPrimaryGreen,
                          fontWeight: FontWeight.w600)),
                ),
                const Spacer(),
                StatusBadge(status: c['status'] ?? 'pending'),
              ],
            ),
            SizedBox(height: 14),
            _detailRow('Name / نام', c['full_name'] ?? ''),
            _detailRow('CNIC / شناخت کارڈ', c['cnic'] ?? ''),
            _detailRow('Mobile / موبائل', c['mobile'] ?? ''),
            _detailRow('Item / شے', c['item_name'] ?? ''),
            _detailRow('Shop / دکان', c['shop_name'] ?? ''),
            _detailRow(
              'Location / مقام',
              c['location_address'] ?? '',
            trailing: _buildViewOnMap(c),

            ),
          ],
        ),
      ),
    );
  }

  Widget? _buildViewOnMap(Map<String, dynamic> c) {
    final lat = c['latitude'];
    final lng = c['longitude'];
    if (lat == null || lng == null) return null;
    final latStr = lat.toString().trim();
    final lngStr = lng.toString().trim();
    final latVal = double.tryParse(latStr);
    final lngVal = double.tryParse(lngStr);
    if (latVal == null || lngVal == null || latVal == 0 || lngVal == 0) return null;

    return GestureDetector(
      onTap: () async {
        final mapsUrl = 'https://www.google.com/maps/search/?api=1&query=$latVal,$lngVal';
        final uri = Uri.parse(mapsUrl);
        try {
          await launchUrl(uri, mode: LaunchMode.externalApplication);
        } catch (_) {
          try {
            await launchUrl(uri, mode: LaunchMode.platformDefault);
          } catch (_) {
            Clipboard.setData(ClipboardData(text: mapsUrl));
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Map URL copied to clipboard')),
              );
            }
          }
        }
      },
      child: Text('View on Map',
          style: TextStyle(
              color: kPrimaryGreen,
              fontSize: 12,
              decoration: TextDecoration.underline)),
    );
  }

  Widget _detailRow(String label, String value, {Widget? trailing}) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 7),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(label,
                style: TextStyle(fontSize: 12.5, color: Colors.grey)),
          ),
          Expanded(
            child: Text(value,
                style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w500,
                    color: Color(0xFF1A1A1A))),
          ),
          if (trailing != null) trailing,
        ],
      ),
    );
  }

  Widget _buildPicturesCard() {
    final pictures = List<dynamic>.from(_complaint!['pictures'] ?? []);
    if (pictures.isEmpty) return const SizedBox();

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Picture / تصویر',
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: kPrimaryGreen)),
            SizedBox(height: 12),
            SizedBox(
              height: 100,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: pictures.length,
                itemBuilder: (_, i) {
                  final url = pictures[i].toString();
                  return Padding(
                    padding: EdgeInsets.only(right: 8),
                    child: GestureDetector(
                      onTap: () => _showFullScreenImage(context, url),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: Image.network(
                          url,
                          width: 100,
                          height: 100,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            width: 100,
                            height: 100,
                            color: Colors.grey.shade200,
                            child: const Icon(Icons.image, color: Colors.grey),
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showFullScreenImage(BuildContext context, String url) {
    showDialog(
      context: context,
      barrierColor: Colors.black,
      builder: (_) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: EdgeInsets.zero,
        child: Stack(
          children: [
            Positioned.fill(
              child: InteractiveViewer(
                minScale: 0.5,
                maxScale: 4,
                child: Center(
                  child: Image.network(
                    url,
                    fit: BoxFit.contain,
                    loadingBuilder: (_, child, progress) =>
                        progress == null ? child : Center(
                          child: CircularProgressIndicator(color: Colors.white),
                        ),
                    errorBuilder: (_, __, ___) => Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.broken_image, color: Colors.white, size: 64),
                        SizedBox(height: 8),
                        Text('Failed to load image',
                            style: TextStyle(color: Colors.white)),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            Positioned(
              top: 40,
              right: 16,
              child: IconButton(
                icon: Icon(Icons.close, color: Colors.white, size: 32),
                onPressed: () => Navigator.pop(context),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDescriptionCard() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      color: Colors.white,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Description / تفصیل',
                style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: kPrimaryGreen)),
            SizedBox(height: 10),
            Text(_complaint!['details'] ?? '',
                style: TextStyle(
                    fontSize: 13, color: Color(0xFF444444), height: 1.5)),
          ],
        ),
      ),
    );
  }

  Widget _buildSubmittedCard() {
    return Container(
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Row(
        children: [
          Icon(Icons.access_time, size: 14, color: Colors.grey),
          SizedBox(width: 6),
          Text(
              'Submitted On: ${_complaint!['submitted_at'] ?? _complaint!['created_date'] ?? ''}',
              style: TextStyle(fontSize: 11.5, color: Colors.grey)),
        ],
      ),
    );
  }

  Widget _buildTimelineCard() {
    final history = List<Map<String, dynamic>>.from(
        _complaint!['status_history'] ?? []);
    if (history.isEmpty) return const SizedBox();

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
                Icon(Icons.timeline, size: 16, color: kPrimaryGreen),
                SizedBox(width: 6),
                Text('Status Timeline / ٹائم لائن',
                    style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: kPrimaryGreen)),
              ],
            ),
            SizedBox(height: 14),
            ...history.asMap().entries.map((e) {
              final i = e.key;
              final log = e.value;
              final isLast = i == history.length - 1;
              final oldStatus = log['old_status'];
              final newStatus = log['new_status'] ?? '';
              final remarks = log['remarks'] ?? '';
              final createdAt = log['created_at'] ?? '';

              Color statusColor(String s) {
                switch (s) {
                  case 'pending': return Colors.orange;
                  case 'in_progress': return Colors.blue;
                  case 'resolved': return Colors.green;
                  case 'rejected': return Colors.red;
                  default: return Colors.grey;
                }
              }

              String statusLabel(String s) {
                switch (s) {
                  case 'pending': return 'Pending';
                  case 'in_progress': return 'In Progress';
                  case 'resolved': return 'Resolved';
                  case 'rejected': return 'Rejected';
                  default: return s;
                }
              }

              return IntrinsicHeight(
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: 24,
                      child: Column(
                        children: [
                          Container(
                            width: 12,
                            height: 12,
                            margin: EdgeInsets.only(top: 4),
                            decoration: BoxDecoration(
                              color: statusColor(newStatus),
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 2),
                              boxShadow: [
                                BoxShadow(
                                    color: statusColor(newStatus).withOpacity(0.3),
                                    blurRadius: 4),
                              ],
                            ),
                          ),
                          if (!isLast)
                            Expanded(
                              child: Container(
                                width: 2,
                                color: Colors.grey.shade300,
                              ),
                            ),
                        ],
                      ),
                    ),
                    SizedBox(width: 10),
                    Expanded(
                      child: Padding(
                        padding: EdgeInsets.only(bottom: isLast ? 0 : 14),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                if (oldStatus != null) ...[
                                  Text(statusLabel(oldStatus),
                                      style: TextStyle(
                                          fontSize: 12,
                                          color: statusColor(oldStatus),
                                          fontWeight: FontWeight.w500)),
                                  Padding(
                                    padding: EdgeInsets.symmetric(horizontal: 4),
                                    child: Icon(Icons.arrow_forward,
                                        size: 13, color: Colors.grey),
                                  ),
                                ],
                                Text(statusLabel(newStatus),
                                    style: TextStyle(
                                        fontSize: 12,
                                        color: statusColor(newStatus),
                                        fontWeight: FontWeight.bold)),
                              ],
                            ),
                            SizedBox(height: 3),
                            Text(createdAt,
                                style: TextStyle(
                                    fontSize: 10.5, color: Colors.grey)),
                            if (remarks.isNotEmpty) ...[
                              SizedBox(height: 2),
                              Text(remarks,
                                  style: TextStyle(
                                      fontSize: 11,
                                      color: Color(0xFF666666),
                                      fontStyle: FontStyle.italic)),
                            ],
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }),
          ],
        ),
      ),
    );
  }
}
