import 'dart:io';
import 'api_service.dart';

class ComplaintService {
  static Future<Map<String, dynamic>> getMyComplaints({
    String? status,
    int page = 1,
  }) async {
    String endpoint = '/complaints?page=$page';
    if (status != null && status != 'All') {
      endpoint += '&status=${status.toLowerCase().replaceAll(' ', '_')}';
    }
    return await ApiService.get(endpoint);
  }

  static Future<Map<String, dynamic>> getComplaintDetail(String id) async {
    return await ApiService.get('/complaints/$id');
  }

  static Future<Map<String, dynamic>> fileComplaint({
    required String fullName,
    required String cnic,
    required String mobile,
    required String itemName,
    required String shopName,
    required double latitude,
    required double longitude,
    required String locationAddress,
    required String details,
    required List<File> pictures,
  }) async {
    final fields = {
      'full_name': fullName,
      'cnic': cnic,
      'mobile': mobile,
      'item_name': itemName,
      'shop_name': shopName,
      'latitude': latitude.toString(),
      'longitude': longitude.toString(),
      'location_address': locationAddress,
      'details': details,
    };
    return await ApiService.postMultipart('/complaints', fields, pictures);
  }

  static Future<Map<String, dynamic>> getDashboardStats() async {
    return await ApiService.get('/complaints/stats');
  }

  static Future<Map<String, dynamic>> trackComplaint(String number) async {
    return await ApiService.get('/complaints/track/$number');
  }
}
