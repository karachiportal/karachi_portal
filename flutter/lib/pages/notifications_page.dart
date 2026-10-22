import 'package:flutter/material.dart';
import '../widgets/shared_widgets.dart';
import '../widgets/offline_banner.dart';
import '../services/notification_service.dart';
import '../services/auth_service.dart';
import '../services/cache_service.dart';
import 'my_complaints_page.dart';

// ════════════════════════════════════════════════════════
//  NOTIFICATIONS PAGE
// ════════════════════════════════════════════════════════
class NotificationsPage extends StatefulWidget {
  final bool standalone;
  const NotificationsPage({super.key, this.standalone = true});
  @override
  State<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends State<NotificationsPage> {
  List<Map<String, dynamic>> _notifications = [];
  bool _loading = true;
  bool _isOffline = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final res = await NotificationService.getNotifications();
    if (mounted) {
      setState(() {
        _loading = false;
        if (res['success'] == true) {
          final data = List<Map<String, dynamic>>.from(
              res['data']?['data'] ?? res['data'] ?? []);
          CacheService.save('cache_notifications', data);
          _notifications = data;
          _isOffline = false;
        }
      });
    }
    if (!res['success'] && mounted) {
      final cached = await CacheService.load('cache_notifications');
      if (cached != null && mounted) {
        setState(() {
          _notifications = List<Map<String, dynamic>>.from(cached);
          _isOffline = true;
        });
      }
    }
  }

  Future<void> _markAllRead() async {
    await NotificationService.markAllAsRead();
    setState(() {
      for (var n in _notifications) {
        n['read_at'] = DateTime.now().toIso8601String();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kLightGreenBg,
      appBar: widget.standalone ? const KarachiAppBar(showBack: true) : null,
      body: Column(
        children: [
          OfflineBanner(isOffline: _isOffline),
          Container(
            color: Colors.white,
            padding: EdgeInsets.fromLTRB(16, 12, 16, 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Notifications',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold)),
                    Text('اطلاعات',
                        style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey,
                            fontFamily: 'NotoNastaliqUrdu')),
                  ],
                ),
                TextButton(
                  onPressed: _markAllRead,
                  child: Text('Mark all read',
                      style: TextStyle(color: kPrimaryGreen, fontSize: 12)),
                ),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(
                    child: CircularProgressIndicator(color: kPrimaryGreen))
                : _notifications.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.notifications_none,
                                size: 56, color: Colors.grey),
                            SizedBox(height: 12),
                            Text('No notifications\nکوئی اطلاع نہیں',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                    color: Colors.grey, fontSize: 14)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        color: kPrimaryGreen,
                        onRefresh: _load,
                        child: ListView.builder(
                          itemCount: _notifications.length,
                          itemBuilder: (_, i) => _notifCard(_notifications[i]),
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _notifCard(Map<String, dynamic> n) {
    final isRead = n['read_at'] != null;
    final typeIcons = {
      'complaint_update': Icons.report_problem_outlined,
      'price_alert': Icons.label_outline,
      'announcement': Icons.campaign_outlined,
      'system': Icons.info_outline,
    };
    final icon = typeIcons[n['type']] ?? Icons.notifications_outlined;

    return Container(
      color: isRead ? Colors.transparent : kPrimaryGreen.withOpacity(0.04),
      child: Column(
        children: [
          ListTile(
            onTap: () async {
              await NotificationService.markAsRead(n['id']);
              setState(() => n['read_at'] = DateTime.now().toIso8601String());
              if (n['type'] == 'complaint_update' && n['data'] != null) {
                final complaintId = n['data']['complaint_id'].toString();
                Navigator.push(context, MaterialPageRoute(
                    builder: (_) => ComplaintDetailPage(complaintId: complaintId)));
              }
            },
            contentPadding:
                EdgeInsets.symmetric(horizontal: 16, vertical: 6),
            leading: Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: kPrimaryGreen.withOpacity(isRead ? 0.07 : 0.15),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: kPrimaryGreen, size: 22),
            ),
            title: Text(n['title'] ?? '',
                style: TextStyle(
                    fontSize: 13.5,
                    fontWeight: isRead ? FontWeight.normal : FontWeight.bold)),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(height: 3),
                Text(n['body'] ?? '',
                    style: TextStyle(fontSize: 12, color: Colors.grey)),
                SizedBox(height: 4),
                Text(n['created_ago'] ?? '',
                    style: TextStyle(fontSize: 10.5, color: Colors.grey)),
              ],
            ),
            trailing: isRead
                ? null
                : Container(
                    width: 8,
                    height: 8,
                    decoration: const BoxDecoration(
                        color: kPrimaryGreen, shape: BoxShape.circle),
                  ),
          ),
          Divider(height: 1, color: Colors.grey.shade100, indent: 16),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════
//  PROFILE PAGE
// ════════════════════════════════════════════════════════
class ProfilePage extends StatefulWidget {
  final bool standalone;
  const ProfilePage({super.key, this.standalone = true});
  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  Map<String, dynamic>? _user;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final u = await AuthService.getCurrentUser();
    if (mounted) setState(() => _user = u);
  }

  Future<void> _logout() async {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Logout / لاگ آؤٹ'),
        content: const Text(
            'Are you sure you want to logout?\nکیا آپ لاگ آؤٹ کرنا چاہتے ہیں؟'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context),
              child:
                  const Text('Cancel', style: TextStyle(color: Colors.grey))),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await AuthService.logout();
              if (mounted) {
                Navigator.pushNamedAndRemoveUntil(
                    context, '/login', (_) => false);
              }
            },
            style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8))),
            child: const Text('Logout', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kLightGreenBg,
      appBar: widget.standalone ? const KarachiAppBar(showBack: true) : null,
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Profile header
            Container(
              color: Colors.white,
              width: double.infinity,
              padding: EdgeInsets.all(24),
              child: Column(
                children: [
                  Container(
                    width: 72,
                    height: 72,
                    decoration: const BoxDecoration(
                        color: Color(0xFFE8F5E9), shape: BoxShape.circle),
                    child:
                        Icon(Icons.person, size: 40, color: kPrimaryGreen),
                  ),
                  SizedBox(height: 12),
                  Text(_user?['name'] ?? 'User',
                      style: TextStyle(
                          fontSize: 18, fontWeight: FontWeight.bold)),
                  SizedBox(height: 4),
                  Text(_user?['username'] ?? '',
                      style: TextStyle(fontSize: 13, color: Colors.grey)),
                ],
              ),
            ),
            SizedBox(height: 12),
            // Info card
            _buildInfoCard(),
            SizedBox(height: 12),
            // Menu items
            _buildMenuCard([
              _MenuItem(
                  Icons.lock_outline, 'Change Password', 'پاس ورڈ تبدیل کریں',
                  () {
                Navigator.pushNamed(context, '/change-password');
              }),
              _MenuItem(
                  Icons.headset_mic_outlined, 'Help & Support', 'مدد اور تعاون',
                  () {
                Navigator.pushNamed(context, '/help');
              }),
              _MenuItem(Icons.info_outline, 'About App', 'ایپ کے بارے میں', () {
                Navigator.pushNamed(context, '/about');
              }),
            ]),
            SizedBox(height: 12),
            // Logout
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 16),
              child: SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: _logout,
                  icon: const Icon(Icons.logout, color: Colors.red),
                  label: const Text('Logout / لاگ آؤٹ',
                      style: TextStyle(
                          color: Colors.red, fontWeight: FontWeight.w600)),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.red),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                    padding: EdgeInsets.symmetric(vertical: 14),
                  ),
                ),
              ),
            ),
            SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoCard() {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16),
      child: Card(
        elevation: 1.5,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        color: Colors.white,
        child: Column(
          children: [
            _infoRow(Icons.badge_outlined, 'CNIC', _user?['cnic'] ?? ''),
            Divider(height: 1, color: Colors.grey.shade100),
            _infoRow(Icons.phone_outlined, 'Mobile', _user?['mobile'] ?? ''),
            Divider(height: 1, color: Colors.grey.shade100),
            _infoRow(Icons.email_outlined, 'Email', _user?['email'] ?? ''),
          ],
        ),
      ),
    );
  }

  Widget _infoRow(IconData icon, String label, String value) {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16, vertical: 13),
      child: Row(
        children: [
          Icon(icon, size: 18, color: kPrimaryGreen),
          SizedBox(width: 10),
          Text(label, style: TextStyle(fontSize: 13, color: Colors.grey)),
          const Spacer(),
          Text(value,
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }

  Widget _buildMenuCard(List<_MenuItem> items) {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16),
      child: Card(
        elevation: 1.5,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        color: Colors.white,
        child: Column(
          children: items.asMap().entries.map((e) {
            final item = e.value;
            final isLast = e.key == items.length - 1;
            return Column(
              children: [
                ListTile(
                  onTap: item.onTap,
                  contentPadding:
                      EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  leading: Container(
                    padding: EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: kPrimaryGreen.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(item.icon, color: kPrimaryGreen, size: 18),
                  ),
                  title: Text(item.label, style: TextStyle(fontSize: 13.5)),
                  subtitle: Text(item.urdu,
                      style: TextStyle(
                          fontSize: 11.5,
                          color: Colors.grey,
                          fontFamily: 'NotoNastaliqUrdu')),
                  trailing: const Icon(Icons.chevron_right, color: Colors.grey),
                ),
                if (!isLast)
                  Divider(height: 1, color: Colors.grey.shade100, indent: 16),
              ],
            );
          }).toList(),
        ),
      ),
    );
  }
}

class _MenuItem {
  final IconData icon;
  final String label;
  final String urdu;
  final VoidCallback onTap;
  _MenuItem(this.icon, this.label, this.urdu, this.onTap);
}
