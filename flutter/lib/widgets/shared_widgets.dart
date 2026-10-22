import 'package:flutter/material.dart';

const Color kPrimaryGreen = Color(0xFF1A5C38);
const Color kDarkGreen = Color(0xFF0D3D22);
const Color kLightGreenBg = Color(0xFFF0F4F1);
const Color kCardBg = Colors.white;

// ═══════════════════════════════════════════════
//  SHARED APP BAR
// ═══════════════════════════════════════════════
class KarachiAppBar extends StatelessWidget implements PreferredSizeWidget {
  final bool showBack;
  final String? title;
  final String? subtitle;
  final int notificationCount;
  final VoidCallback? onNotificationTap;

  const KarachiAppBar({
    super.key,
    this.showBack = false,
    this.title,
    this.subtitle,
    this.notificationCount = 0,
    this.onNotificationTap,
  });

  @override
  Size get preferredSize => Size.fromHeight(64);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: kPrimaryGreen,
      elevation: 0,
      automaticallyImplyLeading: false,
      titleSpacing: 0,
      title: Padding(
        padding: EdgeInsets.symmetric(horizontal: 12),
        child: Row(
          children: [
            if (showBack)
              GestureDetector(
                onTap: () => Navigator.pop(context),
                child: Icon(Icons.arrow_back_ios,
                    color: Colors.white, size: 20),
              ),
            if (!showBack) ...[
              Image.asset('assets/emblem.png',
                  width: 48,
                  height: 48,
                  errorBuilder: (_, __, ___) => Container(
                        width: 48,
                        height: 48,
                        decoration: BoxDecoration(
                            shape: BoxShape.circle, color: kPrimaryGreen),
                        child: Image.asset('assets/logo.png',
                            width: 48, height: 48, fit: BoxFit.cover),
                      )),
              SizedBox(width: 10),
            ],
            Expanded(
              child: title != null
                  ? Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(title!,
                            style: TextStyle(
                                color: Colors.white,
                                fontSize: 15,
                                fontWeight: FontWeight.bold)),
                        if (subtitle != null)
                          Text(subtitle!,
                              style: TextStyle(
                                  color: Colors.white70, fontSize: 12)),
                      ],
                    )
                  : Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text('COMMISSIONER KARACHI PORTAL',
                            style: TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.3)),
                        Text('کمشنر کراچی پورٹل',
                            style: TextStyle(
                                color: Colors.white70,
                                fontSize: 13,
                                fontFamily: 'NotoNastaliqUrdu')),
                      ],
                    ),
            ),
            Stack(
              children: [
                IconButton(
                  icon: Icon(Icons.notifications_outlined,
                      color: Colors.white, size: 26),
                  onPressed: onNotificationTap,
                ),
                if (notificationCount > 0)
                  Positioned(
                    right: 6,
                    top: 6,
                    child: Container(
                      padding: EdgeInsets.all(3),
                      decoration: const BoxDecoration(
                          color: Colors.red, shape: BoxShape.circle),
                      child: Text('$notificationCount',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.bold)),
                    ),
                  ),
              ],
            ),
            if (!showBack)
              IconButton(
                icon: Icon(Icons.account_circle_outlined,
                    color: Colors.white, size: 26),
                onPressed: () => Navigator.pushNamed(context, '/profile'),
              ),
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════
//  BOTTOM NAVIGATION BAR
// ═══════════════════════════════════════════════
class KarachiBottomNav extends StatelessWidget {
  final int currentIndex;
  final int notificationCount;
  final Function(int) onTap;

  const KarachiBottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
    this.notificationCount = 0,
  });

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Container(
        height: 72,
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
                color: Colors.black.withOpacity(0.08),
                blurRadius: 12,
                offset: Offset(0, -2)),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            _navItem(context, 0, Icons.dashboard_outlined, Icons.dashboard,
                'Dashboard', 'ڈیش بورڈ'),
            _navItem(context, 1, Icons.report_problem_outlined,
                Icons.report_problem, 'Complaint', 'شکایت کریں'),
            _centerElevatedButton(
                context, 2, Icons.label, 'Price List', 'قیمت فہرست'),
            _navItemWithBadge(
                context,
                3,
                Icons.notifications_outlined,
                Icons.notifications,
                'Notifications',
                'اطلاعات',
                notificationCount),
            _navItem(context, 4, Icons.person_outline, Icons.person,
                'Profile', 'پروفائل'),
          ],
        ),
      ),
    );
  }

  Widget _centerElevatedButton(BuildContext context, int index, IconData icon,
      String label, String urdu) {
    final isActive = currentIndex == index;
    return Transform.translate(
      offset: Offset(0, -7),
      child: GestureDetector(
        onTap: () => onTap(index),
        child: FittedBox(
          fit: BoxFit.scaleDown,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: isActive
                      ? kPrimaryGreen
                      : kPrimaryGreen.withOpacity(0.85),
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                        color: kPrimaryGreen.withOpacity(0.35),
                        blurRadius: 10,
                        offset: Offset(0, 3))
                  ],
                ),
                child: Icon(icon, color: Colors.white, size: 24),
              ),
              SizedBox(height: 1),
              Text(label,
                  style: TextStyle(
                      fontSize: 10,
                      color: kPrimaryGreen,
                      fontWeight: FontWeight.w600)),
              Text(urdu,
                  style: TextStyle(
                      fontSize: 9,
                      color: kPrimaryGreen,
                      fontFamily: 'NotoNastaliqUrdu')),
            ],
          ),
        ),
      ),
    );
  }

  Widget _navItem(BuildContext context, int index, IconData icon,
      IconData activeIcon, String label, String urdu) {
    final isActive = currentIndex == index;
    return GestureDetector(
      onTap: () => onTap(index),
      child: FittedBox(
        fit: BoxFit.scaleDown,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            SizedBox(height: 8),
            Icon(isActive ? activeIcon : icon,
                color: isActive ? kPrimaryGreen : Colors.grey, size: 22),
            SizedBox(height: 2),
            Text(label,
                style: TextStyle(
                    fontSize: 10,
                    color: isActive ? kPrimaryGreen : Colors.grey,
                    fontWeight:
                        isActive ? FontWeight.w600 : FontWeight.normal)),
            Text(urdu,
                style: TextStyle(
                    fontSize: 9,
                    color: isActive ? kPrimaryGreen : Colors.grey,
                    fontFamily: 'NotoNastaliqUrdu')),
          ],
        ),
      ),
    );
  }

  Widget _navItemWithBadge(BuildContext context, int index, IconData icon,
      IconData activeIcon, String label, String urdu, int count) {
    final isActive = currentIndex == index;
    return GestureDetector(
      onTap: () => onTap(index),
      child: FittedBox(
        fit: BoxFit.scaleDown,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            SizedBox(height: 8),
            Stack(
              children: [
                Icon(isActive ? activeIcon : icon,
                    color: isActive ? kPrimaryGreen : Colors.grey, size: 22),
                if (count > 0)
                  Positioned(
                    right: 0,
                    top: 0,
                    child: Container(
                      padding: EdgeInsets.all(2),
                      decoration: const BoxDecoration(
                          color: Colors.red, shape: BoxShape.circle),
                      child: Text('$count',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 8,
                              fontWeight: FontWeight.bold)),
                    ),
                  ),
              ],
            ),
            SizedBox(height: 2),
            Text(label,
                style: TextStyle(
                    fontSize: 10,
                    color: isActive ? kPrimaryGreen : Colors.grey,
                    fontWeight:
                        isActive ? FontWeight.w600 : FontWeight.normal)),
            Text(urdu,
                style: TextStyle(
                    fontSize: 9,
                    color: isActive ? kPrimaryGreen : Colors.grey,
                    fontFamily: 'NotoNastaliqUrdu')),
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════
//  SECTION HEADER widget
// ═══════════════════════════════════════════════
class SectionHeader extends StatelessWidget {
  final String title;
  final String titleUrdu;
  final String? actionLabel;
  final VoidCallback? onAction;

  const SectionHeader({
    super.key,
    required this.title,
    required this.titleUrdu,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title,
                  style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: kPrimaryGreen)),
              Text(titleUrdu,
                  style: TextStyle(
                      fontSize: 12,
                      color: kPrimaryGreen,
                      fontFamily: 'NotoNastaliqUrdu')),
            ],
          ),
          if (actionLabel != null)
            GestureDetector(
              onTap: onAction,
              child: Row(
                children: [
                  Text(actionLabel!,
                      style: TextStyle(
                          fontSize: 12,
                          color: kPrimaryGreen,
                          fontWeight: FontWeight.w600)),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

// ═══════════════════════════════════════════════
//  STATUS BADGE widget
// ═══════════════════════════════════════════════
class StatusBadge extends StatelessWidget {
  final String status;

  const StatusBadge({super.key, required this.status});

  @override
  Widget build(BuildContext context) {
    Color bg, text;
    String label, urdu;

    switch (status.toLowerCase()) {
      case 'approved':
        bg = const Color(0xFFE8F5E9);
        text = const Color(0xFF2E7D32);
        label = 'Approved';
        urdu = 'منظور شدہ';
        break;
      case 'in_progress':
      case 'in progress':
        bg = const Color(0xFFE3F2FD);
        text = const Color(0xFF1565C0);
        label = 'In Progress';
        urdu = 'جاری';
        break;
      case 'pending':
        bg = const Color(0xFFFFF8E1);
        text = const Color(0xFFF57F17);
        label = 'Pending';
        urdu = 'زیر التوا';
        break;
      case 'resolved':
        bg = const Color(0xFFE8F5E9);
        text = const Color(0xFF1B5E20);
        label = 'Resolved';
        urdu = 'حل شدہ';
        break;
      case 'rejected':
        bg = const Color(0xFFFFEBEE);
        text = const Color(0xFFC62828);
        label = 'Rejected';
        urdu = 'مسترد شدہ';
        break;
      default:
        bg = Colors.grey.shade100;
        text = Colors.grey.shade700;
        label = status;
        urdu = '';
    }

    return Container(
      padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration:
          BoxDecoration(color: bg, borderRadius: BorderRadius.circular(6)),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(label,
              style: TextStyle(
                  color: text, fontSize: 11, fontWeight: FontWeight.w700)),
          if (urdu.isNotEmpty)
            Text(urdu,
                style: TextStyle(
                    color: text,
                    fontSize: 10,
                    fontFamily: 'NotoNastaliqUrdu')),
        ],
      ),
    );
  }
}