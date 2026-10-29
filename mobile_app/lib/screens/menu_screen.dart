import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/product_provider.dart';
import '../widgets/category_chip.dart';
import '../widgets/product_card.dart';

class MenuScreen extends StatelessWidget {
  const MenuScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    const crimsonRed = Color(0xFFD32F2F);
    final productProvider = context.watch<ProductProvider>();
    final filteredProducts = productProvider.filteredProducts;
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

    return Scaffold(
      backgroundColor: const Color(0xFFFDFDF5),
      appBar: AppBar(
        backgroundColor: crimsonRed,
        elevation: 0,
        centerTitle: true,
        title: const Text(
          'เมนูสินค้าทั้งหมด',
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await context.read<ProductProvider>().loadProducts();
        },
        color: crimsonRed,
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 1200),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 16),

                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: categories
                          .map(
                            (cat) => CategoryChip(
                              label: cat,
                              isSelected: productProvider.selectedCategory == cat,
                              onTap: () {
                                productProvider.setSelectedCategory(cat);
                              },
                            ),
                          )
                          .toList(),
                    ),
                  ),

                  const SizedBox(height: 16),

                  Text(
                    'พบสินค้าทั้งหมด (${filteredProducts.length} รายการ)',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Colors.black54,
                    ),
                  ),

                  const SizedBox(height: 12),

                  Expanded(
                    child: productProvider.isLoading
                        ? const Center(
                            child: CircularProgressIndicator(color: crimsonRed),
                          )
                        : filteredProducts.isEmpty
                            ? const Center(
                                child: Text(
                                  'ไม่พบรายการสินค้า',
                                  style: TextStyle(fontSize: 16, color: Colors.grey),
                                ),
                              )
                            : GridView.builder(
                                padding: const EdgeInsets.only(bottom: 24),
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
