class PriceCategory {
  final String slug;
  final String name;
  final String urdu;
  final String icon;
  final int count;
  const PriceCategory({required this.slug, required this.name, required this.urdu, required this.icon, required this.count});
  factory PriceCategory.fromJson(Map<String, dynamic> json) => PriceCategory(
    slug: json['slug'] ?? '', name: json['name'] ?? '', urdu: json['urdu'] ?? '',
    icon: json['icon'] ?? '📦', count: json['count'] ?? 0,
  );
}

class PriceItem {
  final int id;
  final String name;
  final String? nameUrdu;
  final String unit;
  final String? unitUrdu;
  final double price;
  final double previousPrice;
  final double priceChange;
  final double changePercent;
  final String? categorySlug;
  final String? imageUrl;

  const PriceItem({required this.id, required this.name, this.nameUrdu,
    required this.unit, this.unitUrdu, required this.price,
    required this.previousPrice, required this.priceChange,
    required this.changePercent, this.categorySlug, this.imageUrl});

  factory PriceItem.fromJson(Map<String, dynamic> json) => PriceItem(
    id: json['id'], name: json['name'] ?? '',
    nameUrdu: json['name_urdu'], unit: json['unit'] ?? '1 Kg',
    unitUrdu: json['unit_urdu'],
    price: (json['price'] ?? 0.0).toDouble(),
    previousPrice: (json['previous_price'] ?? 0.0).toDouble(),
    priceChange: (json['price_change'] ?? 0.0).toDouble(),
    changePercent: (json['change_percent'] ?? 0.0).toDouble(),
    categorySlug: json['category_slug'], imageUrl: json['image_url'],
  );
}
