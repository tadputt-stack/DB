import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/product_provider.dart';
import '../widgets/category_chip.dart';
import '../widgets/product_card.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({Key? key}) : super(key: key);

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final PageController _bannerController = PageController();
  final PageController _recommendedController = PageController();
  int _currentBannerIndex = 0;
  int _currentRecommendedIndex = 0;
  Timer? _bannerTimer;

  final List<Map<String, String>> _bannerItems = [
    {
      'title': 'NEW ARRIVAL: PREDATOR ELITE',
      'subtitle': 'สัมผัสการควบคุมลูกฟุตบอลระดับโลก',
      'image': 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?w=1000',
    },
    {
      'title': 'SPEED REFINED: ADIDAS F50',
      'subtitle': 'ปลดล็อกความเร็วสูงสุดในสนาม',
      'image': 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1000',
    },
    {
      'title': 'CLASSIC TOUCH: COPA PURE',
      'subtitle': 'สัมผัสความนุ่มพรีเมียมด้วยหนังแท้',
      'image': 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=1000',
    },
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ProductProvider>().loadProducts();
    });

    _bannerTimer = Timer.periodic(const Duration(seconds: 4), (timer) {
      if (_bannerController.hasClients) {
        int nextPage = (_currentBannerIndex + 1) % _bannerItems.length;
        _bannerController.animateToPage(
          nextPage,
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOut,
        );
      }
    });
  }

  @override
  void dispose() {
    _bannerTimer?.cancel();
    _bannerController.dispose();
    _recommendedController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    const crimsonRed = Color(0xFFD32F2F);
    final productProvider = context.watch<ProductProvider>();
    final filteredProducts = productProvider.filteredProducts;
    final recommendedProducts = productProvider.recommendedProducts;

    final categories = ['ทั้งหมด', 'Predator', 'F50', 'Copa'];

    final screenWidth = MediaQuery.of(context).size.width;
    final int crossAxisCount;
    final double childAspectRatio;

    if (screenWidth < 600) {
      crossAxisCount = 2;
      childAspectRatio = 0.65;
    } else if (screenWidth < 900) {
      crossAxisCount = 3;
      childAspectRatio = 0.72;
    } else if (screenWidth < 1200) {
      crossAxisCount = 4;
      childAspectRatio = 0.75;
    } else {
      crossAxisCount = 5;
      childAspectRatio = 0.75;
    }

    final int recommendedPerPage = screenWidth < 600 ? 2 : (screenWidth < 900 ? 3 : 4);

    return Scaffold(
      backgroundColor: const Color(0xFFFDFDF5),
      appBar: AppBar(
        backgroundColor: crimsonRed,
        elevation: 0,
        title: const Row(
          children: [
            Icon(Icons.sports_soccer, color: Colors.white, size: 28),
            SizedBox(width: 8),
            Text(
              'OnlySport',
              style: TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
                fontSize: 22,
                letterSpacing: 0.8,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_none, color: Colors.white),
            onPressed: () {},
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await context.read<ProductProvider>().loadProducts();
        },
        color: crimsonRed,
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 1200),
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.only(bottom: 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 12),

                  SizedBox(
                    height: screenWidth < 600 ? 180 : 250,
                    child: PageView.builder(
                      controller: _bannerController,
                      onPageChanged: (index) {
                        setState(() {
                          _currentBannerIndex = index;
                        });
                      },
                      itemCount: _bannerItems.length,
                      itemBuilder: (context, index) {
                        final item = _bannerItems[index];
                        return Container(
                          margin: const EdgeInsets.symmetric(horizontal: 16),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.12),
                                blurRadius: 8,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: Stack(
                              fit: StackFit.expand,
                              children: [
                                Image.network(
                                  item['image']!,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Container(
                                    color: crimsonRed,
                                  ),
                                ),
                                Container(
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      begin: Alignment.centerLeft,
                                      end: Alignment.centerRight,
                                      colors: [
                                        Colors.black.withOpacity(0.8),
                                        Colors.black.withOpacity(0.2),
                                      ],
                                    ),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.all(20),
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item['title']!,
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold,
                                          letterSpacing: 0.5,
                                        ),
                                      ),
                                      const SizedBox(height: 6),
                                      Text(
                                        item['subtitle']!,
                                        style: const TextStyle(
                                          color: Colors.white70,
                                          fontSize: 13,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 10),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(
                      _bannerItems.length,
                      (index) => AnimatedContainer(
                        duration: const Duration(milliseconds: 300),
                        margin: const EdgeInsets.symmetric(horizontal: 4),
                        width: _currentBannerIndex == index ? 20 : 8,
                        height: 8,
                        decoration: BoxDecoration(
                          color: _currentBannerIndex == index
                              ? crimsonRed
                              : Colors.grey[400],
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 20),

                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: const [
                        Text(
                          'สินค้าแนะนำ',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.black87,
                          ),
                        ),
                        Icon(Icons.star, color: crimsonRed, size: 20),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  if (recommendedProducts.isNotEmpty)
                    Column(
                      children: [
                        SizedBox(
                          height: 280,
                          child: PageView.builder(
                            controller: _recommendedController,
                            onPageChanged: (index) {
                              setState(() {
                                _currentRecommendedIndex = index;
                              });
                            },
                            itemCount: (recommendedProducts.length / recommendedPerPage).ceil(),
                            itemBuilder: (context, pageIndex) {
                              final startIndex = pageIndex * recommendedPerPage;
                              return Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                child: Row(
                                  children: List.generate(recommendedPerPage, (i) {
                                    final itemIndex = startIndex + i;
                                    if (itemIndex < recommendedProducts.length) {
                                      return Expanded(
                                        child: Padding(
                                          padding: EdgeInsets.only(
                                            right: i < recommendedPerPage - 1 ? 12 : 0,
                                          ),
                                          child: ProductCard(
                                            product: recommendedProducts[itemIndex],
                                          ),
                                        ),
                                      );
                                    } else {
                                      return const Expanded(child: SizedBox());
                                    }
                                  }),
                                ),
                              );
                            },
                          ),
                        ),
                        const SizedBox(height: 8),

                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(
                            (recommendedProducts.length / recommendedPerPage).ceil(),
                            (index) => AnimatedContainer(
                              duration: const Duration(milliseconds: 300),
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              width: _currentRecommendedIndex == index ? 16 : 6,
                              height: 6,
                              decoration: BoxDecoration(
                                color: _currentRecommendedIndex == index
                                    ? crimsonRed
                                    : Colors.grey[400],
                                borderRadius: BorderRadius.circular(3),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),

                  const SizedBox(height: 24),

                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16),
                    child: Text(
                      'หมวดหมู่สินค้า',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.black87,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      children: categories
                          .map(
                            (category) => CategoryChip(
                              label: category,
                              isSelected: productProvider.selectedCategory == category,
                              onTap: () {
                                productProvider.setSelectedCategory(category);
                              },
                            ),
                          )
                          .toList(),
                    ),
                  ),

                  const SizedBox(height: 16),

                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: productProvider.isLoading
                        ? const Center(
                            child: Padding(
                              padding: EdgeInsets.all(32),
                              child: CircularProgressIndicator(color: crimsonRed),
                            ),
                          )
                        : filteredProducts.isEmpty
                            ? const Center(
                                child: Padding(
                                  padding: EdgeInsets.all(32),
                                  child: Text('ไม่พบสินค้าในหมวดหมู่นี้'),
                                ),
                              )
                            : GridView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: filteredProducts.length,
                                gridDelegate:
                                    SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: crossAxisCount,
                                  childAspectRatio: childAspectRatio,
                                  crossAxisSpacing: 12,
                                  mainAxisSpacing: 12,
                                ),
                                itemBuilder: (context, index) {
                                  return ProductCard(
                                    product: filteredProducts[index],
                                  );
                                },
                              ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
