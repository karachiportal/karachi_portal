import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class AuthService {
  static Map<String, dynamic>? _currentUser;

  static Future<Map<String, dynamic>> login(
      String username, String password) async {
    final result = await ApiService.post(
      '/auth/login',
      {'username': username, 'password': password},
      auth: false,
    );
    if (result['success'] == true) {
      await ApiService.saveToken(result['token']);
      await _saveUser(result['user']);
    }
    return result;
  }

  static Future<Map<String, dynamic>> register(Map<String, String> data) async {
    final result = await ApiService.post('/auth/register', data, auth: false);
    if (result['success'] == true) {
      await ApiService.saveToken(result['token']);
      await _saveUser(result['user']);
    }
    return result;
  }

  static Future<void> logout() async {
    await ApiService.post('/auth/logout', {});
    await ApiService.clearToken();
    _currentUser = null;
  }

  static Future<Map<String, dynamic>?> getCurrentUser() async {
    if (_currentUser != null) return _currentUser;
    final prefs = await SharedPreferences.getInstance();
    final json = prefs.getString('user_data');
    if (json != null) _currentUser = jsonDecode(json);
    return _currentUser;
  }

  static Future<void> _saveUser(Map<String, dynamic> user) async {
    _currentUser = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user_data', jsonEncode(user));
  }

  static Future<bool> isLoggedIn() async {
    final token = await ApiService.getToken();
    return token != null;
  }
}
