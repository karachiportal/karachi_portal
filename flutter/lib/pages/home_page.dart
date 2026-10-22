import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:hijri/hijri_calendar.dart';
import 'package:hijri/digits_converter.dart';
import '../services/auth_service.dart';
import '../services/complaint_service.dart';
import '../services/notification_service.dart';
import '../services/cache_service.dart';
import '../widgets/shared_widgets.dart';
import '../widgets/offline_banner.dart';
import 'my_complaints_page.dart';
import 'price_list_page.dart';
import 'notifications_page.dart';
import 'file_complaint_page.dart';


class HomePage extends StatefulWidget {
  const HomePage({super.key});
  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _currentIndex = 0;
  int _notifCount = 0;
  bool _isOffline = false;
  Map<String, dynamic>? _user;
  Map<String, dynamic> _stats = {
    'total': 0,
    'pending': 0,
    'in_progress': 0,
    'resolved': 0
  };
  List<Map<String, dynamic>> _recentComplaints = [];
  bool _loading = true;
  String get _today => DateFormat('d MMM yyyy').format(DateTime.now());
  String get _todayH {
    HijriCalendar.setLocal('ar');
    final h = HijriCalendar.now();
    final d = DigitsConverter.convertWesternNumberToEastern(h.hDay);
    final y = DigitsConverter.convertWesternNumberToEastern(h.hYear);
    return '$d ${h.longMonthName} $y ھ';
  }

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    bool offline = false;
    try {
      _user = await AuthService.getCurrentUser();

      final statsRes = await ComplaintService.getDashboardStats();
      final notifRes = await NotificationService.getUnreadCount();
      final recentRes = await ComplaintService.getMyComplaints(page: 1);

      if (statsRes['success'] == true) {
        _stats = Map<String, dynamic>.from(statsRes['data'] ?? _stats);
        await CacheService.save('cache_dashboard_stats', _stats);
      } else {
        final cached = await CacheService.load('cache_dashboard_stats');
        if (cached != null) _stats = Map<String, dynamic>.from(cached);
        offline = true;
      }

      if (notifRes['success'] == true) {
        _notifCount = notifRes['count'] ?? 0;
        await CacheService.save('cache_notif_count', _notifCount);
      } else {
        final cached = await CacheService.load('cache_notif_count');
        if (cached != null) {
          _notifCount =
              cached is int ? cached : int.tryParse(cached.toString()) ?? 0;
        }
        offline = true;
      }

      if (recentRes['success'] == true) {
        final list = recentRes['data']?['data'] ?? [];
        _recentComplaints = List<Map<String, dynamic>>.from(
            list.take(3).map((e) => Map<String, dynamic>.from(e)));
        await CacheService.save('cache_recent_complaints', _recentComplaints);
      } else {
        final cached = await CacheService.load('cache_recent_complaints');
        if (cached != null) {
          _recentComplaints = List<Map<String, dynamic>>.from(cached);
        }
        offline = true;
      }
    } catch (_) {
      offline = true;
    }
    if (mounted) {
      setState(() {
        _isOffline = offline;
        _loading = false;
      });
    }
  }

  void _onNavTap(int index) {
    if (index == _currentIndex) return;
    setState(() => _currentIndex = index);
  }

  @override
  Widget build(BuildContext context) {
    final pages = [
      _dashboardBody(),
      const MyComplaintsPage(standalone: false),
      const PriceListPage(standalone: false),
      const NotificationsPage(standalone: false),
      const ProfilePage(standalone: false),
    ];

    return Scaffold(
      backgroundColor: kLightGreenBg,
      appBar: KarachiAppBar(
        notificationCount: _notifCount,
        onNotificationTap: () => setState(() => _currentIndex = 3),
      ),
      body: pages[_currentIndex],
      bottomNavigationBar: KarachiBottomNav(
        currentIndex: _currentIndex,
        notificationCount: _notifCount,
        onTap: _onNavTap,
      ),
    );
  }

  // ─────────────────────────────────────────────
  //  DASHBOARD BODY
  // ─────────────────────────────────────────────
  Widget _dashboardBody() {
    return Column(
      children: [
        OfflineBanner(isOffline: _isOffline),
        Expanded(
          child: RefreshIndicator(
            color: kPrimaryGreen,
            onRefresh: _loadData,
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(height: 16),
                  _welcomeCard(),
                  SizedBox(height: 8),
                  SectionHeader(
                    title: 'Quick Access',
                    titleUrdu: 'فوری رسائی',
                    actionLabel: 'View All / تمام دیکھیں',
                    onAction: () {},
                  ),
                  _quickAccessGrid(),
                  SizedBox(height: 4),
                  const SectionHeader(
                    title: 'Dashboard Overview',
                    titleUrdu: 'خلاصہ جائزہ',
                  ),
                  _statsRow(),
                  SizedBox(height: 4),
                  SectionHeader(
                    title: 'Recent Complaints',
                    titleUrdu: 'حالیہ شکایات',
                    actionLabel: 'View All / تمام دیکھیں',
                    onAction: () => setState(() => _currentIndex = 1),
                  ),
                  _recentComplaintsList(),
                  SizedBox(height: 20),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  // ─────────────────────────────────────────────
  Widget _welcomeCard() {
    final name = _user?['name'] ?? 'User';
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16),
      child: Card(
        elevation: 2,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        color: kCardBg,
        child: Padding(
          padding: EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: Colors.grey.shade200,
                  shape: BoxShape.circle,
                ),
                child: Icon(Icons.person, size: 32, color: Colors.grey),
              ),
              SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Welcome back,',
                        style: TextStyle(fontSize: 12, color: Colors.grey)),
                    Text(name,
                        style: TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1A1A1A))),
                    Text('خوش آمدید',
                        style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey,
                            fontFamily: 'NotoNastaliqUrdu')),
                  ],
                ),
              ),
              Container(
                width: 1,
                height: 50,
                color: Colors.grey.shade200,
                margin: EdgeInsets.symmetric(horizontal: 12),
              ),
              Column(
                children: [
                  Container(
                    padding: EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: kPrimaryGreen.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: Icon(Icons.calendar_today,
                        color: kPrimaryGreen, size: 18),
                  ),
                  SizedBox(height: 4),
                  Text(_today,
                      style: TextStyle(
                          fontSize: 12, fontWeight: FontWeight.w600)),
                  Text(_todayH,
                      style: TextStyle(
                          fontSize: 10,
                          color: Colors.grey,
                          fontFamily: 'NotoNastaliqUrdu')),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────
  Widget _quickAccessGrid() {
    final items = [
      _QuickItem(Icons.list_alt_outlined, 'My Complaints', 'میری شکایات',
          const Color(0xFF1A5C38), () {
        setState(() => _currentIndex = 1);
      }),
      _QuickItem(Icons.add_circle_outline, 'New Complaint', 'نئی شکایت',
          const Color(0xFF1565C0), () {
        Navigator.push(context,
            MaterialPageRoute(builder: (_) => const FileComplaintPage()));
      }),
      _QuickItem(Icons.label_outline, 'Price List', 'قیمت فہرست',
          const Color(0xFF6A1B9A), () {
        setState(() => _currentIndex = 2);
      }),
      _QuickItem(Icons.notifications_outlined, 'Notifications', 'اطلاعات',
          const Color(0xFFE65100), () {
        setState(() => _currentIndex = 3);
      }, badge: _notifCount),
      _QuickItem(Icons.search_outlined, 'Track Complaint', 'شکایت ٹریک کریں',
          const Color(0xFF00695C), () {
        Navigator.pushNamed(context, '/track-complaint');
      }),
      _QuickItem(Icons.headset_mic_outlined, 'Help & Support', 'مدد اور تعاون',
          const Color(0xFF795548), () {
        Navigator.pushNamed(context, '/help');
      }),
    ];

    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 3,
          mainAxisExtent: 138,
          mainAxisSpacing: 10,
          crossAxisSpacing: 10,
        ),
        itemCount: items.length,
        itemBuilder: (context, index) => _quickAccessCard(items[index]),
      ),
    );
  }

  Widget _quickAccessCard(_QuickItem item) {
    return GestureDetector(
      onTap: item.onTap,
      child: Card(
        elevation: 1.5,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        color: kCardBg,
        child: Padding(
          padding: EdgeInsets.all(10),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Stack(
                alignment: Alignment.topRight,
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: item.color,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(item.icon, color: Colors.white, size: 24),
                  ),
                  if (item.badge != null && item.badge! > 0)
                    Container(
                      padding: EdgeInsets.all(3),
                      decoration: const BoxDecoration(
                          color: Colors.red, shape: BoxShape.circle),
                      child: Text('${item.badge}',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.bold)),
                    ),
                ],
              ),
              SizedBox(height: 8),
              Flexible(
                child: FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(item.label,
                          textAlign: TextAlign.center,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF1A1A1A))),
                      Text(item.urdu,
                          textAlign: TextAlign.center,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                              fontSize: 10,
                              color: Colors.grey,
                              fontFamily: 'NotoNastaliqUrdu')),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────
  Widget _statsRow() {
    final stats = [
      _StatItem('${_stats['total'] ?? 0}', 'Total', 'کل',
          Icons.receipt_long_outlined, const Color(0xFF1A5C38)),
      _StatItem('${_stats['pending'] ?? 0}', 'Pending', 'زیر التوا',
          Icons.hourglass_empty, const Color(0xFF1565C0)),
      _StatItem('${_stats['in_progress'] ?? 0}', 'In Progress', 'جاری',
          Icons.check_circle_outline, const Color(0xFFF57F17)),
      _StatItem('${_stats['resolved'] ?? 0}', 'Resolved', 'حل شدہ',
          Icons.cancel_outlined, const Color(0xFF2E7D32)),
    ];

    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16),
      child: Card(
        elevation: 1.5,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        color: kCardBg,
        child: Padding(
          padding: EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          child: Row(
            children: stats.map((s) => Expanded(child: _statCell(s))).toList(),
          ),
        ),
      ),
    );
  }

  Widget _statCell(_StatItem s) {
    return Column(
      children: [
        Container(
          padding: EdgeInsets.all(8),
          decoration: BoxDecoration(
              color: s.color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10)),
          child: Icon(s.icon, color: s.color, size: 20),
        ),
        SizedBox(height: 6),
        Text(s.count,
            style: TextStyle(
                fontSize: 20, fontWeight: FontWeight.bold, color: s.color)),
        Text(s.label,
            style: TextStyle(fontSize: 10.5, color: Color(0xFF555555))),
        Text(s.urdu,
            style: TextStyle(
                fontSize: 9.5,
                color: Colors.grey,
                fontFamily: 'NotoNastaliqUrdu')),
      ],
    );
  }

  // ─────────────────────────────────────────────
  Widget _recentComplaintsList() {
    if (_loading) {
      return Center(
          child: Padding(
              padding: EdgeInsets.all(20),
              child: const CircularProgressIndicator(color: kPrimaryGreen)));
    }

    if (_recentComplaints.isEmpty) {
      return Padding(
        padding: EdgeInsets.all(20),
        child: const Center(
          child: Column(
            children: [
              Icon(Icons.inbox_outlined, size: 40, color: Colors.grey),
              SizedBox(height: 8),
              Text('No complaints yet\nابھی کوئی شکایت نہیں',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.grey)),
            ],
          ),
        ),
      );
    }

    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16),
      child: Card(
        elevation: 1.5,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        color: kCardBg,
        child: Column(
          children: _recentComplaints
              .asMap()
              .entries
              .map((e) => _complaintRow(e.value,
                  divider: e.key < _recentComplaints.length - 1))
              .toList(),
        ),
      ),
    );
  }

  Widget _complaintRow(Map<String, dynamic> c, {bool divider = true}) {
    final icons = {
      'price': Icons.label_outline,
      'food': Icons.fastfood_outlined,
    };
    final colors = {
      'price': const Color(0xFF1A5C38),
      'food': const Color(0xFFE65100),
    };
    final type = (c['item_name'] ?? '').toString().toLowerCase();
    final iconColor = colors.entries
        .firstWhere((e) => type.contains(e.key),
            orElse: () => const MapEntry('', Color(0xFF1565C0)))
        .value;

    return Column(
      children: [
        ListTile(
          onTap: () {
            Navigator.pushNamed(context, '/complaint-detail',
                arguments: c['id']);
          },
          contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 4),
          leading: Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
                color: iconColor.withOpacity(0.12), shape: BoxShape.circle),
            child: Icon(Icons.report_problem_outlined,
                color: iconColor, size: 20),
          ),
          title: Text(c['item_name'] ?? 'Complaint',
              style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13.5)),
          subtitle: Text(c['complaint_number'] ?? '',
              style: TextStyle(fontSize: 11.5, color: Colors.grey)),
          trailing: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              StatusBadge(status: c['status'] ?? 'pending'),
              SizedBox(width: 6),
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(c['created_date'] ?? '',
                      style: TextStyle(fontSize: 10.5, color: Colors.grey)),
                  Icon(Icons.chevron_right, size: 16, color: Colors.grey),
                ],
              ),
            ],
          ),
        ),
        if (divider)
          Divider(
              height: 1,
              indent: 14,
              endIndent: 14,
              color: Colors.grey.shade100),
      ],
    );
  }
}

// ─────────────────────────────────────────────
//  Data classes
// ─────────────────────────────────────────────
class _QuickItem {
  final IconData icon;
  final String label;
  final String urdu;
  final Color color;
  final VoidCallback onTap;
  final int? badge;
  _QuickItem(this.icon, this.label, this.urdu, this.color, this.onTap,
      {this.badge});
}

class _StatItem {
  final String count;
  final String label;
  final String urdu;
  final IconData icon;
  final Color color;
  _StatItem(this.count, this.label, this.urdu, this.icon, this.color);
}