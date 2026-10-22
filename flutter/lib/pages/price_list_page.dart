import 'package:flutter/material.dart';
import 'package:intl/intl.dart' hide TextDirection;
import '../widgets/shared_widgets.dart';
import '../widgets/offline_banner.dart';
import '../services/price_service.dart';
import '../services/cache_service.dart';
import 'price_search_page.dart';
import 'login_page.dart';

class PriceListPage extends StatefulWidget {
  final bool standalone;
  const PriceListPage({super.key, this.standalone = true});

  @override
  State<PriceListPage> createState() => _PriceListPageState();
}

class _PriceListPageState extends State<PriceListPage> {
  final TextEditingController _searchCtrl = TextEditingController();
  String _selectedCategory = 'all';
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _items = [];
  bool _loading = true;
  bool _isOffline = false;
  String _lastUpdated = '';
  Map<String, String> _categoryIconMap = {};

  final List<Map<String, dynamic>> _fallbackCategories = [
    {'slug': 'all', 'name': 'All Items', 'urdu': 'تمام اشیاء', 'icon': '🟩'},
    {'slug': 'grains', 'name': 'Grains', 'urdu': 'دالیں/اجناس', 'icon': '🌾'},
    {'slug': 'pulses', 'name': 'Pulses', 'urdu': 'دالیں', 'icon': '🫘'},
    {
      'slug': 'cooking_oil',
      'name': 'Cooking Oil',
      'urdu': 'کوکنگ آئل',
      'icon': '🫙'
    },
    {
      'slug': 'sugar_salt',
      'name': 'Sugar & Salt',
      'urdu': 'چینی اور نمک',
      'icon': '🧂'
    },
    {
      'slug': 'vegetables',
      'name': 'Vegetables',
      'urdu': 'سبزیاں',
      'icon': '🥦'
    },
    {'slug': 'fruits', 'name': 'Fruits', 'urdu': 'پھل', 'icon': '🍎'},
  ];

  @override
  void initState() {
    super.initState();
    _loadCategories();
    _loadPrices();
  }

  Future<void> _loadCategories() async {
    // Show last-known REAL data (from cache) immediately if we have any,
    // instead of the hardcoded _fallbackCategories list — the hardcoded
    // list can never reflect admin-panel changes/deletions, so showing it
    // even briefly means a deleted/changed icon can flash back on every
    // launch. Cache always reflects what was actually last fetched.
    final cachedFirst = await CacheService.load('cache_categories');
    if (cachedFirst != null && mounted) {
      final data = List<Map<String, dynamic>>.from(cachedFirst);
      setState(() {
        _categories = data;
        _categoryIconMap = {
          for (var c in data) (c['slug'] ?? '') as String: c['icon'] ?? '📦'
        };
      });
    }

    final res = await PriceService.getCategories();
    if (res['success'] == true && mounted) {
      final data = List<Map<String, dynamic>>.from(res['data'] ?? []);
      await CacheService.save('cache_categories', res['data']);
      setState(() {
        _isOffline = false;
        _categories = data;
        _categoryIconMap = {
          for (var c in data) (c['slug'] ?? '') as String: c['icon'] ?? '📦'
        };
      });
    } else if (mounted) {
      if (cachedFirst == null) {
        // Truly nothing available (first-ever launch, no internet) —
        // hardcoded placeholder is the only option left.
        setState(() {
          _isOffline = true;
          _categories = _fallbackCategories;
          _categoryIconMap = {
            for (var c in _fallbackCategories)
              c['slug'] as String: c['icon'] as String? ?? '📦'
          };
        });
      } else {
        setState(() => _isOffline = true);
      }
    }
  }

  Future<void> _loadPrices({String? search}) async {
    setState(() => _loading = true);
    final cacheKey = 'cache_prices_${_selectedCategory}';

    List<Map<String, dynamic>> allItems = [];
    bool anySuccess = false;
    String? lastUpdated;
    int page = 1;
    int lastPage = 1;

    // Fetch every page for this category (not just page 1) so "All Items"
    // and large categories (Vegetables, Fruits, ...) show their full list.
    while (page <= lastPage && page <= 50) {
      final res = await PriceService.getPriceList(
        category: _selectedCategory == 'all' ? null : _selectedCategory,
        search: search,
        page: page,
      );
      if (res['success'] != true) break;
      anySuccess = true;
      final data = res['data'];
      final pageItems = List<Map<String, dynamic>>.from(
          data is Map ? (data['data'] ?? []) : (data ?? []));
      allItems.addAll(pageItems);
      lastUpdated ??= res['last_updated'];
      if (data is Map && data['last_page'] != null) {
        lastPage = int.tryParse(data['last_page'].toString()) ?? page;
      } else {
        lastPage = page;
      }
      page++;
    }

    if (!mounted) return;
    if (anySuccess) {
      await CacheService.save(cacheKey, allItems);
      setState(() {
        _items = allItems;
        _isOffline = false;
        _lastUpdated = lastUpdated ??
            DateFormat('d MMM yyyy, hh:mm a').format(DateTime.now());
        _loading = false;
      });
    } else {
      final cached = await CacheService.load(cacheKey);
      if (cached != null && mounted) {
        setState(() {
          _items = List<Map<String, dynamic>>.from(cached);
          _isOffline = true;
          _loading = false;
        });
      } else if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kLightGreenBg,
      appBar: widget.standalone
          ? AppBar(
              backgroundColor: kPrimaryGreen,
              elevation: 0,
              automaticallyImplyLeading: false,
              titleSpacing: 0,
              title: Padding(
                padding: EdgeInsets.only(left: 8),
                child: Row(
                  children: [
                    GestureDetector(
                      onTap: () => Navigator.pop(context),
                      child: Icon(Icons.arrow_back_ios,
                          color: Colors.white, size: 20),
                    ),
                    SizedBox(width: 8),
                    Text('Prices / قیمتیں',
                        style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
              actions: [
                TextButton.icon(
                  onPressed: () => Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginPage()),
                  ),
                  icon: Icon(Icons.login, color: Colors.white, size: 18),
                  label: Text('Login / لاگ ان',
                      style: TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w600)),
                ),
              ],
            )
          : null,
      body: Column(
        children: [
          OfflineBanner(isOffline: _isOffline),
          Expanded(
            child: widget.standalone
                ? SingleChildScrollView(
                    child: Column(
                      children: [
                        _buildComplaintCard(),
                        _buildHeader(),
                        _buildSearch(),
                        _buildCategories(),
                        _buildUpdateBanner(),
                        _buildTable(shrinkWrap: true),
                      ],
                    ),
                  )
                : Column(
                    children: [
                      _buildHeader(),
                      _buildSearch(),
                      _buildCategories(),
                      _buildUpdateBanner(),
                      Expanded(child: _buildTable()),
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      color: Colors.white,
      padding: EdgeInsets.fromLTRB(16, 14, 16, 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: kPrimaryGreen,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(Icons.label_outline, color: Colors.white, size: 22),
          ),
          SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Item Price List   اشیاء کی قیمت فہرست',
                    style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1A1A1A))),
                SizedBox(height: 2),
                Text(
                    'Daily updated prices of essential items in Karachi\nکراچی میں ضروری اشیاء کی روزانہ قیمتیں',
                    style: TextStyle(fontSize: 11, color: Colors.grey)),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Container(
                padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: kPrimaryGreen.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.calendar_today,
                        size: 12, color: kPrimaryGreen),
                    SizedBox(width: 4),
                    Text(DateFormat('d MMM yyyy').format(DateTime.now()),
                        style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: kPrimaryGreen)),
                  ],
                ),
              ),
              SizedBox(height: 2),
              Text('تازہ ترین اپڈیٹ',
                  style: TextStyle(
                      fontSize: 10,
                      color: Colors.grey,
                      fontFamily: 'NotoNastaliqUrdu')),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSearch() {
    return Container(
      color: Colors.white,
      padding: EdgeInsets.fromLTRB(16, 0, 16, 10),
      child: Row(
        children: [
          Expanded(
            child: SizedBox(
              height: 42,
              child: TextField(
                controller: _searchCtrl,
                style: TextStyle(fontSize: 13),
                decoration: InputDecoration(
                  hintText: 'Search item / چیز تلاش کریں',
                  hintStyle: TextStyle(color: Colors.grey, fontSize: 13),
                  prefixIcon:
                      Icon(Icons.search, size: 18, color: Colors.grey),
                  suffixIcon: _searchCtrl.text.isNotEmpty
                      ? IconButton(
                          icon: Icon(Icons.close,
                              size: 16, color: Colors.grey),
                          onPressed: () {
                            _searchCtrl.clear();
                            _loadPrices();
                          })
                      : null,
                  filled: true,
                  fillColor: Colors.grey.shade100,
                  contentPadding: EdgeInsets.zero,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide.none,
                  ),
                ),
                onSubmitted: (v) => _loadPrices(search: v),
              ),
            ),
          ),
          SizedBox(width: 8),
          GestureDetector(
            onTap: () {
              Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const PriceSearchPage()));
            },
            child: Container(
              height: 42,
              padding: EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: kPrimaryGreen.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: kPrimaryGreen.withOpacity(0.3)),
              ),
              child: Row(
                children: [
                  Icon(Icons.tune, size: 16, color: kPrimaryGreen),
                  SizedBox(width: 4),
                  Text('Filters\nفلتر',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                          fontSize: 10,
                          color: kPrimaryGreen,
                          fontWeight: FontWeight.w600)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCatIcon(dynamic icon) {
    final s = icon?.toString() ?? '';
    if (s.startsWith('http://') || s.startsWith('https://')) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: Image.network(s, width: 28, height: 28, fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _catIconFallback('📦')),
      );
    }
    return _catIconFallback(s.isEmpty ? '📦' : s);
  }

  // Fixed 28x28 footprint for every icon variant (real emoji, missing/fallback
  // box icon) so the chip never resizes depending on which one is showing.
  Widget _catIconFallback(String icon) {
    return SizedBox(
      width: 28,
      height: 28,
      child: FittedBox(
        fit: BoxFit.contain,
        child: Text(icon, style: TextStyle(fontSize: 20)),
      ),
    );
  }

  Widget _buildCategories() {
    final cats = _categories.isEmpty ? _fallbackCategories : _categories;
    return Container(
      color: Colors.white,
      height: 82,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        itemCount: cats.length,
        itemBuilder: (_, i) {
          final cat = cats[i];
          final isSelected = _selectedCategory == cat['slug'];
          return GestureDetector(
            onTap: () {
              setState(() => _selectedCategory = cat['slug']);
              _loadPrices();
            },
            child: Container(
              margin: EdgeInsets.only(right: 10),
              padding: EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: isSelected ? kPrimaryGreen : Colors.grey.shade100,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _buildCatIcon(cat['icon']),
                  SizedBox(height: 2),
                  Text(cat['name'] ?? '',
                      style: TextStyle(
                          fontSize: 11,
                          color: isSelected ? Colors.white : Colors.black87,
                          fontWeight: FontWeight.w600)),
                  Text(cat['urdu'] ?? '',
                      style: TextStyle(
                          fontSize: 9,
                          color: isSelected ? Colors.white70 : Colors.grey,
                          fontFamily: 'NotoNastaliqUrdu')),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildUpdateBanner() {
    return Container(
      margin: EdgeInsets.fromLTRB(16, 10, 16, 0),
      padding: EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: kPrimaryGreen.withOpacity(0.07),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: kPrimaryGreen.withOpacity(0.2)),
      ),
      child: Row(
        children: [
          Icon(Icons.refresh, size: 14, color: kPrimaryGreen),
          SizedBox(width: 6),
          Expanded(
            child: Text(
              'Prices updated on $_lastUpdated\nقیمتیں $_lastUpdated بجے اپڈیٹ کی گئیں',
              style: TextStyle(fontSize: 11, color: Color(0xFF444444)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTable({bool shrinkWrap = false}) {
    if (_loading) {
      return const Center(
          child: CircularProgressIndicator(color: kPrimaryGreen));
    }
    if (_items.isEmpty) {
      return const Center(
          child: Text('No items found', style: TextStyle(color: Colors.grey)));
    }

    return _buildTableContent(shrinkWrap: shrinkWrap);
  }

  Widget _buildTableContent({bool shrinkWrap = false}) {
    final body = Padding(
      padding: EdgeInsets.fromLTRB(16, 10, 16, 0),
      child: Column(
        children: [
          Card(
              elevation: 1.5,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              clipBehavior: Clip.hardEdge,
              child: Column(
                children: [
                  Container(
                    color: kPrimaryGreen,
                    padding:
                        EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    child: Row(
                      children: [
                        Expanded(
                            flex: 3,
                            child: Text('Item\nاشیاء',
                                style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12))),
                        Expanded(
                            child: Text('Unit\nاکائی',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12))),
                        Expanded(
                            child: Text('Price\n(Rs.)',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12))),
                        Expanded(
                            flex: 2,
                            child: Text('Change\nتبدیلی',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12))),
                      ],
                    ),
                  ),
                  ..._items.asMap().entries.map((e) {
                    return _priceRow(e.value,
                        isEven: e.key % 2 == 0,
                        isLast: e.key == _items.length - 1);
                  }),
                ],
              )),
          _buildDisclaimer(),
        ],
      ),
    );
    if (shrinkWrap) return body;
    return SingleChildScrollView(child: body);
  }

  Widget _priceRow(Map<String, dynamic> item,
      {bool isEven = true, bool isLast = false}) {
    final change = (item['price_change'] ?? 0.0).toDouble();
    final changePct = (item['change_percent'] ?? 0.0).toDouble();
    final isUp = change > 0;
    final isDown = change < 0;
    final changeColor = isUp
        ? Colors.red
        : isDown
            ? Colors.green
            : Colors.grey;

    return GestureDetector(
      onTap: () {
        Navigator.push(
            context,
            MaterialPageRoute(
                builder: (_) => PriceSearchPage(initialItem: item)));
      },
      child: Container(
        color: isEven ? Colors.white : Colors.grey.shade50,
        padding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        child: Row(
          children: [
            Expanded(
              flex: 3,
              child: Row(
                children: [
                  if (item['image_url'] != null)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: Image.network(
                        item['image_url'],
                        width: 34,
                        height: 34,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) =>
                            _itemIcon(item['category_slug'] ?? ''),
                      ),
                    )
                  else
                    _itemIcon(item['category_slug'] ?? ''),
                  SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item['name'] ?? '',
                            style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w600)),
                        if (item['name_urdu'] != null)
                          Text(item['name_urdu'],
                              style: TextStyle(
                                  fontSize: 10.5,
                                  color: Colors.grey,
                                  fontFamily: 'NotoNastaliqUrdu')),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: FittedBox(
                fit: BoxFit.scaleDown,
                child: Text(item['unit'] ?? '1 Kg',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 12)),
              ),
            ),
            Expanded(
              child: FittedBox(
                fit: BoxFit.scaleDown,
                child: Text((item['price'] ?? 0.0).toStringAsFixed(2),
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        fontSize: 12.5, fontWeight: FontWeight.w600)),
              ),
            ),
            Expanded(
              flex: 2,
              child: FittedBox(
                fit: BoxFit.scaleDown,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (isUp)
                          Icon(Icons.arrow_upward, size: 12, color: Colors.red)
                        else if (isDown)
                          Icon(Icons.arrow_downward,
                              size: 12, color: Colors.green)
                        else
                          const Text('—',
                              style: TextStyle(color: Colors.grey)),
                        if (change != 0)
                          Text(
                            '${isUp ? '+' : ''}${change.toStringAsFixed(2)}',
                            style: TextStyle(
                                fontSize: 11,
                                color: changeColor,
                                fontWeight: FontWeight.w600),
                          ),
                      ],
                    ),
                    if (change != 0)
                      Text(
                        '(${changePct.toStringAsFixed(2)}%)',
                        style: TextStyle(fontSize: 10, color: changeColor),
                      ),
                    if (change == 0)
                      Text('0.00%',
                          style: TextStyle(fontSize: 10, color: Colors.grey)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _itemIcon(String slug) {
    final icon = _categoryIconMap[slug] ?? '📦';
    final s = icon.toString();
    if (s.startsWith('http://') || s.startsWith('https://')) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(17),
        child: Image.network(s, width: 34, height: 34, fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _itemIconFallback('📦')),
      );
    }
    return _itemIconFallback(s);
  }

  Widget _itemIconFallback(String icon) {
    return Container(
      width: 34, height: 34,
      decoration: BoxDecoration(
        color: kPrimaryGreen.withOpacity(0.1),
        shape: BoxShape.circle,
      ),
      child: Center(
        child: Text(icon, style: TextStyle(fontSize: 18)),
      ),
    );
  }

  Widget _buildDisclaimer() {
    return Container(
      margin: EdgeInsets.all(16),
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: kPrimaryGreen.withOpacity(0.07),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: kPrimaryGreen.withOpacity(0.2)),
      ),
      child: Row(
        children: [
          Container(
            padding: EdgeInsets.all(6),
            decoration: BoxDecoration(
                color: kPrimaryGreen, borderRadius: BorderRadius.circular(8)),
            child: Icon(Icons.info_outline, color: Colors.white, size: 14),
          ),
          SizedBox(width: 10),
          Expanded(
            child: Text(
              'Prices are indicative and may vary depending on market.\nقیمتیں اشارتی ہیں اور مارکیٹ کے مطابق مختلف ہو سکتی ہیں۔',
              style: TextStyle(fontSize: 11, color: Color(0xFF444444)),
            ),
          ),
          SizedBox(width: 8),
          Column(
            children: [
              Text('Disclaimer',
                  style: TextStyle(
                      fontSize: 10,
                      color: kPrimaryGreen,
                      fontWeight: FontWeight.w600)),
              Text('دستبرداری',
                  style: TextStyle(
                      fontSize: 9,
                      color: kPrimaryGreen,
                      fontFamily: 'NotoNastaliqUrdu')),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildComplaintCard() {
    return Container(
      margin: EdgeInsets.fromLTRB(16, 12, 16, 12),
      padding: EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: kPrimaryGreen.withOpacity(0.3)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 6,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            children: [
              Container(
                padding: EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: kPrimaryGreen.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(Icons.report_problem_outlined,
                    color: kPrimaryGreen, size: 22),
              ),
              SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('File a Complaint',
                        style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1A1A1A))),
                    Text('شکایت درج کریں',
                        style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey,
                            fontFamily: 'NotoNastaliqUrdu')),
                  ],
                ),
              ),
            ],
          ),
          SizedBox(height: 12),
          Text(
            'Report overpricing or any issue with GPS location & photos.\nاوور پرائسنگ یا کسی بھی مسئلے کی اطلاع دیں جی پی ایس لوکیشن اور تصاویر کے ساتھ۔',
            style: TextStyle(fontSize: 12, color: Color(0xFF555555)),
          ),
          SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 44,
            child: ElevatedButton.icon(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                      builder: (_) => const LoginPage()),
                );
              },
              icon: Icon(Icons.lock_outline, size: 18, color: Colors.white),
              label: Text(
                'Login to File / شکایت درج کرنے کے لیے لاگ ان کریں',
                style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                    fontSize: 14),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: kPrimaryGreen,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}