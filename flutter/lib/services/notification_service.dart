import 'api_service.dart';

class NotificationService {
  static Future<Map<String, dynamic>> getNotifications({int page = 1}) async {
    return await ApiService.get('/notifications?page=$page');
  }

  static Future<Map<String, dynamic>> markAsRead(int id) async {
    return await ApiService.post('/notifications/$id/read', {});
  }

  static Future<Map<String, dynamic>> markAllAsRead() async {
    return await ApiService.post('/notifications/read-all', {});
  }

  static Future<Map<String, dynamic>> getUnreadCount() async {
    return await ApiService.get('/notifications/unread-count');
  }
}
