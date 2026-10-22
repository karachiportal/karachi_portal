import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'price_list_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _rememberMe = false;
  bool _obscurePassword = true;

  static const Color primaryGreen = Color(0xFF1A5C38);
  static const Color lightGreen = Color(0xFF2E7D52);
  static const Color bgColor = Color(0xFFF0F4F1);

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: bgColor,
      body: SingleChildScrollView(
        child: Column(
          children: [
            // ── Header with green background and Karachi skyline ──
            _buildHeader(),

            // ── Title ──
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              child: Column(
                children: [
                  Text(
                    'COMMISSIONER KARACHI PORTAL',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w900,
                      color: primaryGreen,
                      letterSpacing: 0.5,
                    ),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'کمشنر کراچی پورٹل',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: primaryGreen,
                      fontFamily: 'NotoNastaliqUrdu',
                    ),
                  ),
                  SizedBox(height: 10),
                  _buildDividerWithDiamond(),
                ],
              ),
            ),

            // ── Login Form Card ──
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                color: Colors.white,
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Username field
                        _buildBilingualLabel(
                            'CNIC / Phone Number', 'شناختی کارڈ / فون نمبر'),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _usernameController,
                          hint:
                              'Enter CNIC or Phone Number / شناختی کارڈ یا فون نمبر درج کریں',
                          prefixIcon: Icons.badge_outlined,
                          validator: (v) => v!.isEmpty
                              ? 'CNIC یا فون نمبر درج کریں'
                              : null,
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Use your CNIC (with or without dashes) or registered phone number',
                          style: TextStyle(
                              fontSize: 11, color: Colors.grey.shade600),
                        ),
                        Text(
                          'اپنا شناختی کارڈ (ڈیش کے ساتھ یا بغیر)  یا  رجسٹرڈ فون نمبر درج کریں',
                          style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey.shade600,
                              fontFamily: 'NotoNastaliqUrdu'),
                        ),
                        SizedBox(height: 16),

                        // Password field
                        _buildBilingualLabel('Password', 'پاس ورڈ'),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _passwordController,
                          hint: 'Enter your password / پاس ورڈ درج کریں',
                          prefixIcon: Icons.lock_outline,
                          obscureText: _obscurePassword,
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscurePassword
                                  ? Icons.visibility_off_outlined
                                  : Icons.visibility_outlined,
                              color: Colors.grey,
                            ),
                            onPressed: () => setState(
                              () => _obscurePassword = !_obscurePassword,
                            ),
                          ),
                          validator: (v) =>
                              v!.isEmpty ? 'Password درج کریں' : null,
                        ),
                        SizedBox(height: 12),

                        // Remember Me
                        Row(
                          children: [
                            SizedBox(
                              width: 24,
                              height: 24,
                              child: Checkbox(
                                value: _rememberMe,
                                activeColor: primaryGreen,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                onChanged: (v) =>
                                    setState(() => _rememberMe = v!),
                              ),
                            ),
                            SizedBox(width: 8),
                            Text(
                              'Remember Me / مجھی یاد رکھیں',
                              style: TextStyle(
                                fontSize: 13.5,
                                color: Color(0xFF333333),
                              ),
                            ),
                          ],
                        ),
                        SizedBox(height: 20),

                        // Login Button
                        SizedBox(
                          width: double.infinity,
                          height: 52,
                          child: ElevatedButton.icon(
                            onPressed: () async {
                              if (_formKey.currentState!.validate()) {
                                final res = await AuthService.login(
                                  _usernameController.text.trim(),
                                  _passwordController.text,
                                );
                                if (res['success'] == true) {
                                  Navigator.pushReplacementNamed(
                                    context,
                                    '/home',
                                  );
                                } else {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                        res['message'] ?? 'Login failed',
                                      ),
                                    ),
                                  );
                                }
                              }
                            },
                            icon: const Icon(
                              Icons.lock_outline,
                              color: Colors.white,
                            ),
                            label: Text(
                              'لاگ ان کریں  /  Login',
                              style: TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: primaryGreen,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10),
                              ),
                              elevation: 2,
                            ),
                          ),
                        ),
                        SizedBox(height: 16),

                        // Forgot Password | Help
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            _buildTextLink(
                              icon: Icons.lock_reset_outlined,
                              line1: 'Forgot Password?',
                              line2: 'پاس ورڈ بھول گئے؟',
                              onTap: () {},
                            ),
                            Container(
                              width: 1,
                              height: 36,
                              color: Colors.grey.shade300,
                            ),
                            _buildTextLink(
                              icon: Icons.headset_mic_outlined,
                              line1: 'Help',
                              line2: 'مدد',
                              onTap: () {},
                            ),
                          ],
                        ),

                        SizedBox(height: 12),

                        // Sign up link
                        Center(
                          child: GestureDetector(
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const SignUpPage(),
                                ),
                              );
                            },
                            child: RichText(
                              text: TextSpan(
                                style: TextStyle(
                                  fontSize: 13.5,
                                  color: Color(0xFF555555),
                                ),
                                children: [
                                  const TextSpan(text: 'New user? '),
                                  TextSpan(
                                    text: 'Register / رجسٹر کریں',
                                    style: TextStyle(
                                      color: primaryGreen,
                                      fontWeight: FontWeight.bold,
                                      decoration: TextDecoration.underline,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),

                        SizedBox(height: 16),

                        // ── or divider ──
                        Row(
                          children: [
                            Expanded(
                                child: Divider(color: Colors.grey.shade300)),
                            Padding(
                              padding: EdgeInsets.symmetric(horizontal: 12),
                              child: Text('or / یا',
                                  style: TextStyle(
                                      color: Colors.grey, fontSize: 13)),
                            ),
                            Expanded(
                                child: Divider(color: Colors.grey.shade300)),
                          ],
                        ),
                        SizedBox(height: 16),

                        // ── Browse as Guest butto0n ──
                        SizedBox(
                          width: double.infinity,
                          height: 44,
                          child: OutlinedButton.icon(
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) =>
                                      const PriceListPage(standalone: true),
                                ),
                              );
                            },
                            icon: Icon(Icons.visibility_outlined,
                                color: primaryGreen),
                            label: Text(
                              'Browse as Guest / بطور مہمان دیکھیں',
                              style: TextStyle(
                                color: primaryGreen,
                                fontWeight: FontWeight.w600,
                                fontSize: 14,
                              ),
                            ),
                            style: OutlinedButton.styleFrom(
                              side: BorderSide(color: primaryGreen),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10),
                              ),
                            ),
                          ),
                        ),
                        SizedBox(height: 8),
                        Center(
                          child: Text(
                            'Login for full access\nمکمل رسائی کے لیے لاگ ان کریں',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                                fontSize: 12, color: Colors.grey.shade600),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),

            SizedBox(height: 24),
            // ── Footer skyline ──
            _buildFooter(),
          ],
        ),
      ),
    );
  }

  // ────────────────────────────────────────────────
  //  Reusable Widgets
  // ────────────────────────────────────────────────

  Widget _buildHeader() {
    return Stack(
      alignment: Alignment.bottomCenter,
      clipBehavior: Clip.none,
      children: [
        // Image background with light green overlay
        Container(
          height: 160,
          width: double.infinity,
          decoration: BoxDecoration(
            image: DecorationImage(
              image: AssetImage('assets/header_bg.png'),
              fit: BoxFit.cover,
              colorFilter: ColorFilter.mode(
                Color(0xFFE8F5E9).withOpacity(0.75),
                BlendMode.softLight,
              ),
            ),
          ),
          child: CustomPaint(painter: _SkylinePainter()),
        ),
        // Bottom fade to background
        Positioned(
          bottom: 0,
          left: 0,
          right: 0,
          height: 40,
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Colors.transparent, Color(0xFFF0F4F1)],
              ),
            ),
          ),
        ),
        // Circular logo
        Positioned(
          bottom: -10,
          child: Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black26,
                  blurRadius: 8,
                  offset: Offset(0, 3),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(50),
              child: Image.asset('assets/logo2.png',
                  width: 68, height: 68, fit: BoxFit.cover),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFooter() {
    return Column(
      children: [
        SizedBox(
          height: 100,
          width: double.infinity,
          child: Image.asset('assets/footer_skyline.png',
              height: 100, fit: BoxFit.cover),
        ),
        SizedBox(height: 8),
        Text(
          'Government of Sindh',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 14,
            color: Color(0xFF333333),
          ),
        ),
        Text(
          'حکومتِ سندھ',
          style: TextStyle(
            fontSize: 14,
            color: Color(0xFF333333),
            fontFamily: 'NotoNastaliqUrdu',
          ),
        ),
        SizedBox(height: 20),
      ],
    );
  }

  Widget _buildDividerWithDiamond() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(width: 60, height: 1, color: const Color(0xFF1A5C38)),
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 6),
          child: Icon(
            Icons.diamond_outlined,
            size: 12,
            color: Color(0xFF1A5C38),
          ),
        ),
        Container(width: 60, height: 1, color: const Color(0xFF1A5C38)),
      ],
    );
  }

  Widget _buildBilingualLabel(String english, String urdu) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          english,
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 14,
            color: Color(0xFF1A1A1A),
          ),
        ),
        Text(
          urdu,
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 14,
            color: Color(0xFF1A1A1A),
            fontFamily: 'NotoNastaliqUrdu',
          ),
        ),
      ],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData prefixIcon,
    bool obscureText = false,
    Widget? suffixIcon,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: obscureText,
      validator: validator,
      style: TextStyle(fontSize: 14),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: TextStyle(color: Colors.grey, fontSize: 13),
        prefixIcon: Icon(prefixIcon, color: Colors.grey, size: 20),
        suffixIcon: suffixIcon,
        contentPadding: EdgeInsets.symmetric(
          vertical: 14,
          horizontal: 12,
        ),
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
          borderSide: const BorderSide(color: Color(0xFF1A5C38), width: 1.5),
        ),
        filled: true,
        fillColor: Colors.white,
      ),
    );
  }

  Widget _buildTextLink({
    required IconData icon,
    required String line1,
    required String line2,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFF1A5C38), size: 20),
          SizedBox(width: 6),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                line1,
                style: TextStyle(
                  color: Color(0xFF1A5C38),
                  fontWeight: FontWeight.w600,
                  fontSize: 12.5,
                ),
              ),
              Text(
                line2,
                style: TextStyle(
                  color: Color(0xFF1A5C38),
                  fontSize: 11.5,
                  fontFamily: 'NotoNastaliqUrdu',
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════
//  SIGN UP PAGE
// ════════════════════════════════════════════════════════

class SignUpPage extends StatefulWidget {
  const SignUpPage({super.key});

  @override
  State<SignUpPage> createState() => _SignUpPageState();
}

class _SignUpPageState extends State<SignUpPage> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _cnicController = TextEditingController();
  final _mobileController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscurePassword = true;
  bool _obscureConfirm = true;

  static const Color primaryGreen = Color(0xFF1A5C38);
  static const Color bgColor = Color(0xFFF0F4F1);

  @override
  void initState() {
    super.initState();
    _cnicController.addListener(_formatCnic);
    _mobileController.addListener(_formatMobile);
  }

  void _formatCnic() {
    String text = _cnicController.text.replaceAll('-', '');
    if (text.length > 13) text = text.substring(0, 13);

    String formatted = '';
    for (int i = 0; i < text.length; i++) {
      if (i == 5 || i == 12) formatted += '-';
      formatted += text[i];
    }

    if (formatted != _cnicController.text) {
      _cnicController.value = TextEditingValue(
        text: formatted,
        selection: TextSelection.collapsed(offset: formatted.length),
      );
    }
  }

  void _formatMobile() {
    String text = _mobileController.text.replaceAll('-', '');
    if (text.length > 11) text = text.substring(0, 11);

    String formatted = '';
    for (int i = 0; i < text.length; i++) {
      if (i == 4) formatted += '-';
      formatted += text[i];
    }

    if (formatted != _mobileController.text) {
      _mobileController.value = TextEditingValue(
        text: formatted,
        selection: TextSelection.collapsed(offset: formatted.length),
      );
    }
  }

  @override
  void dispose() {
    _cnicController.removeListener(_formatCnic);
    _mobileController.removeListener(_formatMobile);
    _nameController.dispose();
    _cnicController.dispose();
    _mobileController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: bgColor,
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header
            _buildHeader(),

            // Title
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              child: Column(
                children: [
                  Text(
                    'COMMISSIONER KARACHI PORTAL',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w900,
                      color: primaryGreen,
                      letterSpacing: 0.5,
                    ),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'نئی رجسٹریشن',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: primaryGreen,
                      fontFamily: 'NotoNastaliqUrdu',
                    ),
                  ),
                  SizedBox(height: 10),
                  _buildDividerWithDiamond(),
                ],
              ),
            ),

            // Form Card
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 20),
              child: Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                color: Colors.white,
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Full Name
                        _buildBilingualLabel('Full Name', 'پورا نام'),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _nameController,
                          hint: 'Enter your full name / پورا نام درج کریں',
                          prefixIcon: Icons.person_outline,
                          keyboardType: TextInputType.name,
                          validator: (v) => v!.isEmpty ? 'نام درج کریں' : null,
                        ),
                        SizedBox(height: 16),

                        // CNIC
                        _buildBilingualLabel('CNIC Number', 'شناختی کارڈ نمبر'),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _cnicController,
                          hint: '42101-1234567-1',
                          prefixIcon: Icons.credit_card_outlined,
                          keyboardType: TextInputType.number,
                          validator: (v) {
                            if (v!.isEmpty) return 'CNIC درج کریں';
                            // basic CNIC format check
                            final cleaned = v.replaceAll('-', '');
                            if (cleaned.length != 13) {
                              return 'CNIC 13 ہندسوں کا ہونا چاہیے';
                            }
                            return null;
                          },
                        ),
                        SizedBox(height: 16),

                        // Mobile Number
                        _buildBilingualLabel('Mobile Number', 'موبائل نمبر'),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _mobileController,
                          hint: '03XX-XXXXXXX',
                          prefixIcon: Icons.phone_outlined,
                          keyboardType: TextInputType.phone,
                          validator: (v) {
                            if (v!.isEmpty) return 'موبائل نمبر درج کریں';
                            if (v.replaceAll('-', '').length < 10) {
                              return 'درست نمبر درج کریں';
                            }
                            return null;
                          },
                        ),
                        SizedBox(height: 16),

                        // Email
                        _buildBilingualLabel('Email Address', 'ای میل پتہ'),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _emailController,
                          hint: 'email@example.com',
                          prefixIcon: Icons.email_outlined,
                          keyboardType: TextInputType.emailAddress,
                          validator: (v) {
                            if (v!.isEmpty) return 'ای میل درج کریں';
                            if (!v.contains('@') || !v.contains('.')) {
                              return 'درست ای میل درج کریں';
                            }
                            return null;
                          },
                        ),
                        SizedBox(height: 16),

                        // Password
                        _buildBilingualLabel('Password', 'پاس ورڈ'),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _passwordController,
                          hint: 'Enter password / پاس ورڈ درج کریں',
                          prefixIcon: Icons.lock_outline,
                          obscureText: _obscurePassword,
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscurePassword
                                  ? Icons.visibility_off_outlined
                                  : Icons.visibility_outlined,
                              color: Colors.grey,
                            ),
                            onPressed: () => setState(
                              () => _obscurePassword = !_obscurePassword,
                            ),
                          ),
                          validator: (v) {
                            if (v!.isEmpty) return 'پاس ورڈ درج کریں';
                            if (v.length < 8) {
                              return 'پاس ورڈ کم از کم 8 حروف ہونا چاہیے';
                            }
                            return null;
                          },
                        ),
                        SizedBox(height: 16),

                        // Confirm Password
                        _buildBilingualLabel(
                          'Confirm Password',
                          'پاس ورڈ تصدیق',
                        ),
                        SizedBox(height: 8),
                        _buildTextField(
                          controller: _confirmPasswordController,
                          hint: 'Re-enter password / دوبارہ درج کریں',
                          prefixIcon: Icons.lock_reset_outlined,
                          obscureText: _obscureConfirm,
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscureConfirm
                                  ? Icons.visibility_off_outlined
                                  : Icons.visibility_outlined,
                              color: Colors.grey,
                            ),
                            onPressed: () => setState(
                              () => _obscureConfirm = !_obscureConfirm,
                            ),
                          ),
                          validator: (v) {
                            if (v!.isEmpty) return 'تصدیق کریں';
                            if (v != _passwordController.text) {
                              return 'پاس ورڈ مطابقت نہیں رکھتا';
                            }
                            return null;
                          },
                        ),
                        SizedBox(height: 24),

                        // Register Button
                        SizedBox(
                          width: double.infinity,
                          height: 52,
                          child: ElevatedButton.icon(
                            onPressed: () async {
                              if (_formKey.currentState!.validate()) {
                                final res = await AuthService.register({
                                  'name': _nameController.text.trim(),
                                  'cnic': _cnicController.text.trim(),
                                  'mobile': _mobileController.text.trim(),
                                  'email': _emailController.text.trim(),
                                  'password': _passwordController.text,
                                  'password_confirmation':
                                      _confirmPasswordController.text,
                                });
                                if (res['success'] == true) {
                                  Navigator.pushReplacementNamed(
                                    context,
                                    '/home',
                                  );
                                } else {
                                  final msg =
                                      res['message'] ?? 'Registration failed';
                                  final errs = res['errors'];
                                  String fullMsg = msg;
                                  if (errs is Map) {
                                    fullMsg = errs.values
                                        .map(
                                          (e) => e is List ? e.join(', ') : e,
                                        )
                                        .join('\n');
                                  }
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(content: Text(fullMsg)),
                                  );
                                }
                              }
                            },
                            icon: const Icon(
                              Icons.how_to_reg_outlined,
                              color: Colors.white,
                            ),
                            label: Text(
                              'رجسٹر کریں  /  Register',
                              style: TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: primaryGreen,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(10),
                              ),
                              elevation: 2,
                            ),
                          ),
                        ),
                        SizedBox(height: 16),

                        // Already have account
                        Center(
                          child: GestureDetector(
                            onTap: () => Navigator.pop(context),
                            child: RichText(
                              text: TextSpan(
                                style: TextStyle(
                                  fontSize: 13.5,
                                  color: Color(0xFF555555),
                                ),
                                children: [
                                  const TextSpan(text: 'Already registered? '),
                                  TextSpan(
                                    text: 'Login / لاگ ان کریں',
                                    style: TextStyle(
                                      color: primaryGreen,
                                      fontWeight: FontWeight.bold,
                                      decoration: TextDecoration.underline,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),

            SizedBox(height: 24),
            _buildFooter(),
          ],
        ),
      ),
    );
  }

  // ────────────────────────────────────────────────
  Widget _buildHeader() {
    return Stack(
      alignment: Alignment.bottomCenter,
      clipBehavior: Clip.none,
      children: [
        Container(
          height: 140,
          width: double.infinity,
          decoration: BoxDecoration(
            image: DecorationImage(
              image: AssetImage('assets/header_bg.png'),
              fit: BoxFit.cover,
              colorFilter: ColorFilter.mode(
                Color(0xFFE8F5E9).withOpacity(0.75),
                BlendMode.softLight,
              ),
            ),
          ),
          child: CustomPaint(painter: _SkylinePainter()),
        ),
        // Bottom fade to background
        Positioned(
          bottom: 0,
          left: 0,
          right: 0,
          height: 35,
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Colors.transparent, Color(0xFFF0F4F1)],
              ),
            ),
          ),
        ),
        Positioned(
          top: 40,
          left: 16,
          child: SafeArea(
            child: IconButton(
              icon: const Icon(Icons.arrow_back_ios, color: Colors.white),
              onPressed: () => Navigator.pop(context),
            ),
          ),
        ),
        Positioned(
          bottom: -10,
          child: Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black26,
                  blurRadius: 8,
                  offset: Offset(0, 3),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(50),
              child: Image.asset('assets/logo2.png',
                  width: 68, height: 68, fit: BoxFit.cover),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFooter() {
    return Column(
      children: [
        SizedBox(
          height: 100,
          width: double.infinity,
          child: Image.asset('assets/footer_skyline.png',
              height: 100, fit: BoxFit.cover),
        ),
        SizedBox(height: 8),
        Text(
          'Government of Sindh',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 14,
            color: Color(0xFF333333),
          ),
        ),
        Text(
          'حکومتِ سندھ',
          style: TextStyle(
            fontSize: 14,
            color: Color(0xFF333333),
            fontFamily: 'NotoNastaliqUrdu',
          ),
        ),
        SizedBox(height: 20),
      ],
    );
  }

  Widget _buildDividerWithDiamond() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(width: 60, height: 1, color: const Color(0xFF1A5C38)),
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 6),
          child: Icon(
            Icons.diamond_outlined,
            size: 12,
            color: Color(0xFF1A5C38),
          ),
        ),
        Container(width: 60, height: 1, color: const Color(0xFF1A5C38)),
      ],
    );
  }

  Widget _buildBilingualLabel(String english, String urdu) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          english,
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 14,
            color: Color(0xFF1A1A1A),
          ),
        ),
        Text(
          urdu,
          style: TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 14,
            color: Color(0xFF1A1A1A),
            fontFamily: 'NotoNastaliqUrdu',
          ),
        ),
      ],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData prefixIcon,
    bool obscureText = false,
    Widget? suffixIcon,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      obscureText: obscureText,
      keyboardType: keyboardType,
      validator: validator,
      style: TextStyle(fontSize: 14),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: TextStyle(color: Colors.grey, fontSize: 13),
        prefixIcon: Icon(prefixIcon, color: Colors.grey, size: 20),
        suffixIcon: suffixIcon,
        contentPadding: EdgeInsets.symmetric(
          vertical: 14,
          horizontal: 12,
        ),
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
          borderSide: const BorderSide(color: Color(0xFF1A5C38), width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Colors.red),
        ),
        filled: true,
        fillColor: Colors.white,
      ),
    );
  }
}

// ════════════════════════════════════════════════════════
//  CUSTOM PAINTERS
// ════════════════════════════════════════════════════════

/// Faint Mazar-e-Quaid silhouette in header background
class _SkylinePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white.withOpacity(0.06)
      ..style = PaintingStyle.fill;

    // Simple dome silhouette (left side - Mazar-e-Quaid style)
    final path = Path();
    // Left arch
    path.moveTo(size.width * 0.05, size.height);
    path.lineTo(size.width * 0.05, size.height * 0.55);
    path.quadraticBezierTo(
      size.width * 0.18,
      size.height * 0.05,
      size.width * 0.31,
      size.height * 0.55,
    );
    path.lineTo(size.width * 0.31, size.height);
    path.close();
    canvas.drawPath(path, paint);

    // Right tower (clock tower style)
    final path2 = Path();
    path2.moveTo(size.width * 0.72, size.height);
    path2.lineTo(size.width * 0.72, size.height * 0.3);
    path2.lineTo(size.width * 0.74, size.height * 0.2);
    path2.lineTo(size.width * 0.76, size.height * 0.3);
    path2.lineTo(size.width * 0.82, size.height * 0.3);
    path2.lineTo(size.width * 0.82, size.height);
    path2.close();
    canvas.drawPath(path2, paint);
  }

  @override
  bool shouldRepaint(_) => false;
}

/// Bottom Karachi city skyline (outline only, light green)
class _KarachiSkylinePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF1A5C38).withOpacity(0.18)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.2;

    final path = Path();
    path.moveTo(0, size.height);

    // Buildings sequence
    final buildings = [
      [0.0, 0.9],
      [0.02, 0.9],
      [0.02, 0.6],
      [0.06, 0.6],
      [0.06, 0.9],
      [0.08, 0.9],
      [0.08, 0.4],
      [0.10, 0.35],
      [0.12, 0.4],
      [0.14, 0.4],
      [0.14, 0.9],
      [0.16, 0.9],
      [0.16, 0.55],
      [0.20, 0.55],
      [0.20, 0.9],
      [0.22, 0.9],
      [0.22, 0.45],
      [0.24, 0.38],
      [0.26, 0.45],
      [0.28, 0.45],
      [0.28, 0.9],
      [0.30, 0.9],
      [0.30, 0.65],
      [0.34, 0.65],
      [0.34, 0.9],
      [0.36, 0.9],
      [0.36, 0.50],
      [0.40, 0.50],
      [0.40, 0.9],
      [0.42, 0.9],
      [0.42, 0.35],
      [0.44, 0.28],
      [0.46, 0.35],
      [0.48, 0.35],
      [0.48, 0.9],
      [0.50, 0.9],
      [0.50, 0.55],
      [0.54, 0.55],
      [0.54, 0.9],
      [0.56, 0.9],
      [0.56, 0.45],
      [0.60, 0.45],
      [0.60, 0.9],
      [0.62, 0.9],
      [0.62, 0.60],
      [0.66, 0.60],
      [0.66, 0.9],
      [0.68, 0.9],
      [0.68, 0.40],
      [0.70, 0.33],
      [0.72, 0.40],
      [0.74, 0.40],
      [0.74, 0.9],
      [0.76, 0.9],
      [0.76, 0.55],
      [0.80, 0.55],
      [0.80, 0.9],
      [0.82, 0.9],
      [0.82, 0.50],
      [0.86, 0.50],
      [0.86, 0.9],
      [0.88, 0.9],
      [0.88, 0.65],
      [0.92, 0.65],
      [0.92, 0.9],
      [0.94, 0.9],
      [0.94, 0.45],
      [0.98, 0.45],
      [0.98, 0.9],
      [1.0, 0.9],
    ];

    for (final b in buildings) {
      path.lineTo(size.width * b[0], size.height * b[1]);
    }

    path.lineTo(size.width, size.height);
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(_) => false;
}
