import 'dart:convert';
import 'dart:io';
// import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // ── Auto-detect: Android emulator → 10.0.2.2, else → 127.0.0.1 ──
  static String get baseUrl {
  return 'https://karachi-portal-production.up.railway.app/api';
}
  static String? _token;

  // ─────────────────────────────────────────────
  //  Token helpers
  // ─────────────────────────────────────────────
  static Future<void> saveToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  static Future<String?> getToken() async {
    if (_token != null) return _token;
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token');
    return _token;
  }

  static Future<void> clearToken() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
  }

  // ─────────────────────────────────────────────
  //  Headers
  // ─────────────────────────────────────────────
  static Future<Map<String, String>> _headers({bool auth = true}) async {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (auth) {
      final token = await getToken();
      if (token != null) headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  // ─────────────────────────────────────────────
  //  Generic request helpers
  // ─────────────────────────────────────────────
  static Future<Map<String, dynamic>> get(String endpoint) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl$endpoint'),
        headers: await _headers(),
      );
      return _handle(response);
    } on SocketException {
      return {'success': false, 'message': 'No internet connection'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> body, {
    bool auth = true,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl$endpoint'),
        headers: await _headers(auth: auth),
        body: jsonEncode(body),
      );
      return _handle(response);
    } on SocketException {
      return {'success': false, 'message': 'No internet connection'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> postMultipart(
    String endpoint,
    Map<String, String> fields,
    List<File> files,
  ) async {
    try {
      final token = await getToken();
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl$endpoint'),
      );
      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';
      request.fields.addAll(fields);
      for (int i = 0; i < files.length; i++) {
        request.files.add(
          await http.MultipartFile.fromPath('pictures[$i]', files[i].path),
        );
      }
      final streamed = await request.send();
      final response = await http.Response.fromStream(streamed);
      return _handle(response);
    } on SocketException {
      return {'success': false, 'message': 'No internet connection'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Future<Map<String, dynamic>> put(
    String endpoint,
    Map<String, dynamic> body,
  ) async {
    try {
      final response = await http.put(
        Uri.parse('$baseUrl$endpoint'),
        headers: await _headers(),
        body: jsonEncode(body),
      );
      return _handle(response);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  static Map<String, dynamic> _handle(http.Response response) {
    try {
      final data = jsonDecode(response.body);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return {'success': true, ...data};
      } else if (response.statusCode == 401) {
        clearToken();
        return {
          'success': false,
          'message': 'Unauthorized. Please login again.',
        };
      } else {
        return {
          'success': false,
          'message': data['message'] ?? 'Server error',
          ...data,
        };
      }
    } catch (_) {
      return {'success': false, 'message': 'Invalid server response'};
    }
  }
}
