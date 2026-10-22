import 'api_service.dart';

class PriceService {
  static Future<Map<String, dynamic>> getCategories() async {
    return await ApiService.get('/prices/categories');
  }

  static Future<Map<String, dynamic>> getPriceList({
    String? category,
    String? search,
    int page = 1,
  }) async {
    String endpoint = '/prices?page=$page';
    if (category != null && category != 'all') {
      endpoint += '&category=$category';
    }
    if (search != null && search.isNotEmpty) {
      endpoint += '&search=${Uri.encodeComponent(search)}';
    }
    return await ApiService.get(endpoint);
  }

  static Future<Map<String, dynamic>> getPriceDetail(int itemId) async {
    return await ApiService.get('/prices/$itemId');
  }

  static Future<Map<String, dynamic>> getPriceTrend(
      int itemId, String period) async {
    return await ApiService.get('/prices/$itemId/trend?period=$period');
  }

  static Future<Map<String, dynamic>> searchItem(String query) async {
    return await ApiService.get(
        '/prices/search?q=${Uri.encodeComponent(query)}');
  }
}
