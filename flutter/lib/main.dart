import 'package:flutter/material.dart';
import 'pages/home_page.dart';
import 'pages/my_complaints_page.dart';
import 'pages/file_complaint_page.dart';
import 'pages/price_list_page.dart';
import 'pages/price_search_page.dart';
import 'pages/notifications_page.dart';
import 'pages/track_complaint_page.dart';
import 'pages/login_page.dart';
import 'services/auth_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const KarachiPortalApp());
}

class KarachiPortalApp extends StatelessWidget {
  const KarachiPortalApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Commissioner Karachi Portal',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primaryColor: const Color(0xFF1A5C38),
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF1A5C38),
          primary: const Color(0xFF1A5C38),
        ),
        fontFamily: 'Roboto',
        scaffoldBackgroundColor: const Color(0xFFF0F4F1),
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFF1A5C38),
          elevation: 0,
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF1A5C38),
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        ),
      ),
      home: const SplashScreen(),
      routes: {
        '/login': (_) => const LoginPage(),
        '/home': (_) => const HomePage(),
        '/complaints': (_) => const MyComplaintsPage(),
        '/file-complaint': (_) => const FileComplaintPage(),
        '/price-list': (_) => const PriceListPage(),
        '/price-search': (_) => const PriceSearchPage(),
        '/notifications': (_) => const NotificationsPage(),
        '/profile': (_) => const ProfilePage(),
        '/track-complaint': (_) => const TrackComplaintPage(),
        '/help': (_) => const _PlaceholderPage('Help & Support'),
        '/about': (_) => const _PlaceholderPage('About'),
        '/change-password': (_) => const _PlaceholderPage('Change Password'),
      },
      onGenerateRoute: (settings) {
        if (settings.name == '/complaint-detail') {
          final id = settings.arguments.toString();
          return MaterialPageRoute(
            builder: (_) => ComplaintDetailPage(complaintId: id),
          );
        }
        return null;
      },
    );
  }
}

// ─────────────────────────────────────────────
//  Splash Screen
// ─────────────────────────────────────────────
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});
  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _navigate();
  }

  Future<void> _navigate() async {
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;
    final loggedIn = await AuthService.isLoggedIn();
    Navigator.pushReplacementNamed(context, loggedIn ? '/home' : '/login');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A5C38),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(70),
              child: Image.asset('assets/logo.png',
                  width: 120, height: 120, fit: BoxFit.cover),
            ),
            const SizedBox(height: 20),
            const Text(
              'COMMISSIONER KARACHI PORTAL',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              'کمشنر کراچی پورٹل',
              style: TextStyle(
                color: Colors.white70,
                fontSize: 16,
                fontFamily: 'NotoNastaliqUrdu',
              ),
            ),
            const SizedBox(height: 40),
            const CircularProgressIndicator(
              color: Colors.white,
              strokeWidth: 2,
            ),
          ],
        ),
      ),
    );
  }
}

class _PlaceholderPage extends StatelessWidget {
  final String title;
  const _PlaceholderPage(this.title);
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        backgroundColor: const Color(0xFF1A5C38),
      ),
      body: Center(child: Text('$title - Coming Soon')),
    );
  }
}
