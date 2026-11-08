import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/product.dart';
import '../models/product_review.dart';
import '../providers/product_provider.dart';
import '../providers/user_provider.dart';
import '../widgets/category_chip.dart';
import '../widgets/review_card.dart';

class MenuScreen extends StatefulWidget {
  const MenuScreen({Key? key}) : super(key: key);

  @override
  State<MenuScreen> createState() => _MenuScreenState();
}

class _MenuScreenState extends State<MenuScreen> {
  static const crimsonRed = Color(0xFFD32F2F);
  static const starGold = Color(0xFFF59E0B);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = context.read<ProductProvider>();
      if (provider.products.isEmpty) {
        provider.loadProducts();
      }
      if (provider.reviews.isEmpty) {
        provider.loadReviews();
      }
    });
  }

  // -------------------------------------------------------------
  // Dialog: เขียนรีวิวสินค้า (Write a Review)
  // -------------------------------------------------------------
  void _openWriteReviewDialog(BuildContext context, Product product) {
    int selectedScore = 5;
    final nameController = TextEditingController();
    final commentController = TextEditingController();
    bool isSubmitting = false;

    // Prefill name from UserProvider if available
    final currentUser = context.read<UserProvider>().user;
    if (currentUser.name.isNotEmpty && currentUser.name != 'OnlySport Customer') {
      nameController.text = currentUser.name;
    }

    final scoreLabels = [
      '',
      'แย่มาก (1 ดาว)',
      'พอใช้ (2 ดาว)',
      'ปานกลาง (3 ดาว)',
      'ดีมาก (4 ดาว)',
      'ยอดเยี่ยมที่สุด (5 ดาว)',
    ];

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(18),
              ),
              contentPadding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: crimsonRed.withOpacity(0.1),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.rate_review_rounded,
                      color: crimsonRed,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Text(
                      'เขียนรีวิวสินค้า',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1E293B),
                      ),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20, color: Colors.grey),
                    onPressed: isSubmitting ? null : () => Navigator.pop(dialogCtx),
                    visualDensity: VisualDensity.compact,
                    padding: EdgeInsets.zero,
                  ),
                ],
              ),
              content: SingleChildScrollView(
                child: SizedBox(
                  width: 440,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Product mini preview
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Container(
                                width: 44,
                                height: 44,
                                color: const Color(0xFFE2E8F0),
                                child: Image.network(
                                  product.imageUrl,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => const Icon(
                                    Icons.sports_soccer,
                                    size: 22,
                                    color: Colors.grey,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    product.name,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                    ),
                                  ),
                                  Text(
                                    '฿${product.price.toStringAsFixed(0)} • ${product.seriesName}',
                                    style: const TextStyle(
                                      color: crimsonRed,
                                      fontWeight: FontWeight.w600,
                                      fontSize: 12,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Interactive 5-Star Rating Selector
                      const Text(
                        'ให้คะแนนความพึงพอใจ',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF334155),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(5, (idx) {
                          final starNum = idx + 1;
                          final isSelected = starNum <= selectedScore;
                          return InkWell(
                            onTap: isSubmitting
                                ? null
                                : () {
                                    setDialogState(() {
                                      selectedScore = starNum;
                                    });
                                  },
                            borderRadius: BorderRadius.circular(20),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 4, vertical: 4),
                              child: Icon(
                                isSelected
                                    ? Icons.star_rounded
                                    : Icons.star_outline_rounded,
                                color: isSelected ? starGold : const Color(0xFFCBD5E1),
                                size: 34,
                              ),
                            ),
                          );
                        }),
                      ),
                      Center(
                        child: Text(
                          scoreLabels[selectedScore],
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFFB45309),
                          ),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Reviewer Name Field
                      const Text(
                        'ชื่อของคุณ (Reviewer Name)',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF334155),
                        ),
                      ),
                      const SizedBox(height: 6),
                      TextField(
                        controller: nameController,
                        enabled: !isSubmitting,
                        decoration: InputDecoration(
                          hintText: 'เช่น ชนาธิป, นิติกร, Mandy25...',
                          hintStyle: const TextStyle(fontSize: 13, color: Colors.grey),
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: crimsonRed, width: 1.5),
                          ),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Review Comment Textarea
                      const Text(
                        'ความคิดเห็นของคุณ (Review & Feedback)',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF334155),
                        ),
                      ),
                      const SizedBox(height: 6),
                      TextField(
                        controller: commentController,
                        enabled: !isSubmitting,
                        maxLines: 4,
                        decoration: InputDecoration(
                          hintText:
                              'บอกเล่าความรู้สึก สัมผัสหนัง ความกระชับ หรือความคุ้มค่าในการใช้งาน...',
                          hintStyle: const TextStyle(fontSize: 12.5, color: Colors.grey),
                          contentPadding: const EdgeInsets.all(12),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: crimsonRed, width: 1.5),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              actionsPadding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
              actions: [
                TextButton(
                  onPressed: isSubmitting ? null : () => Navigator.pop(dialogCtx),
                  child: const Text(
                    'ยกเลิก',
                    style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                  ),
                ),
                ElevatedButton(
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          final comment = commentController.text.trim();
                          if (comment.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('กรุณากรอกความคิดเห็นรีวิวสินค้าก่อนส่งครับ'),
                                backgroundColor: Colors.orange,
                              ),
                            );
                            return;
                          }

                          setDialogState(() {
                            isSubmitting = true;
                          });

                          final provider = context.read<ProductProvider>();
                          final success = await provider.submitReview(
                            productId: product.id,
                            reviewerName: nameController.text.trim().isEmpty
                                ? 'ผู้ใช้งาน OnlySport'
                                : nameController.text.trim(),
                            score: selectedScore,
                            comment: comment,
                          );

                          if (!mounted) return;
                          Navigator.pop(dialogCtx);

                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Row(
                                children: [
                                  const Icon(Icons.check_circle, color: Colors.white, size: 20),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      success
                                          ? 'ส่งรีวิวสินค้าเรียบร้อยแล้ว ขอบคุณสำหรับความคิดเห็นครับ!'
                                          : 'บันทึกรีวิวในแอปเรียบร้อยแล้ว (ออฟไลน์โหมด)',
                                    ),
                                  ),
                                ],
                              ),
                              backgroundColor: success ? Colors.green[700] : Colors.blueGrey,
                              behavior: SnackBarBehavior.floating,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                              ),
                            ),
                          );
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: crimsonRed,
                    foregroundColor: Colors.white,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                  ),
                  child: isSubmitting
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Text(
                          'ส่งรีวิว',
                          style: TextStyle(fontWeight: FontWeight.bold),
                        ),
                ),
              ],
            );
          },
        );
      },
    );
  }

  // -------------------------------------------------------------
  // BottomSheet: ดูรีวิวทั้งหมดของสินค้านี้ (View All Reviews)
  // -------------------------------------------------------------
  void _openAllReviewsModal(BuildContext context, Product product) {
    final provider = context.read<ProductProvider>();
    final productReviews = provider.getReviewsForProduct(product.id);
    final ratingAvg = provider.getProductRating(product.id);
    final totalCount = provider.getProductReviewCount(product.id);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 0.85,
          ),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Drag Indicator
              Container(
                margin: const EdgeInsets.only(top: 10, bottom: 6),
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: const Color(0xFFCBD5E1),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),

              // Modal Header
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 14),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'รายการรีวิวทั้งหมด (Customer Reviews)',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1E293B),
                            ),
                          ),
                          Text(
                            product.name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 12.5,
                              color: Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, color: Colors.grey),
                      onPressed: () => Navigator.pop(sheetCtx),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1, color: Color(0xFFF1F5F9)),

              // Rating Summary Score Banner
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: Row(
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              ratingAvg.toStringAsFixed(1),
                              style: const TextStyle(
                                fontSize: 32,
                                fontWeight: FontWeight.w900,
                                color: Color(0xFFB45309),
                              ),
                            ),
                            const SizedBox(width: 4),
                            const Text(
                              '/ 5.0',
                              style: TextStyle(
                                fontSize: 14,
                                color: Color(0xFFB45309),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                        Row(
                          children: List.generate(5, (i) {
                            return Icon(
                              i < ratingAvg.round()
                                  ? Icons.star_rounded
                                  : Icons.star_outline_rounded,
                              size: 16,
                              color: starGold,
                            );
                          }),
                        ),
                      ],
                    ),
                    const Spacer(),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          'จากทั้งหมด $totalCount รีวิว',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF92400E),
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          '100% ผู้ซื้อจริงจาก OnlySport',
                          style: TextStyle(
                            fontSize: 11,
                            color: Color(0xFFB45309),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              // Reviews List
              Flexible(
                child: productReviews.isEmpty
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(32),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.rate_review_outlined,
                                size: 48,
                                color: Colors.grey,
                              ),
                              const SizedBox(height: 12),
                              const Text(
                                'ยังไม่มีรีวิวสำหรับสินค้ารุ่นนี้',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF475569),
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'ร่วมแชร์ประสบการณ์สวมใส่เป็นคนแรกได้เลย!',
                                style: TextStyle(fontSize: 13, color: Colors.grey),
                              ),
                              const SizedBox(height: 16),
                              ElevatedButton.icon(
                                onPressed: () {
                                  Navigator.pop(sheetCtx);
                                  _openWriteReviewDialog(context, product);
                                },
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: crimsonRed,
                                  foregroundColor: Colors.white,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                ),
                                icon: const Icon(Icons.edit, size: 16),
                                label: const Text('เขียนรีวิวคนแรก'),
                              ),
                            ],
                          ),
                        ),
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.fromLTRB(20, 4, 20, 20),
                        itemCount: productReviews.length,
                        separatorBuilder: (_, __) =>
                            const Divider(height: 16, color: Color(0xFFF1F5F9)),
                        itemBuilder: (context, idx) {
                          final rev = productReviews[idx];
                          return Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              CircleAvatar(
                                radius: 16,
                                backgroundColor: crimsonRed.withOpacity(0.12),
                                child: Text(
                                  rev.reviewerName.isNotEmpty
                                      ? rev.reviewerName[0].toUpperCase()
                                      : 'U',
                                  style: const TextStyle(
                                    color: crimsonRed,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Text(
                                          rev.reviewerName,
                                          style: const TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 13,
                                            color: Color(0xFF1E293B),
                                          ),
                                        ),
                                        const SizedBox(width: 4),
                                        const Icon(
                                          Icons.verified,
                                          size: 13,
                                          color: Color(0xFF2563EB),
                                        ),
                                        const Spacer(),
                                        Row(
                                          children: List.generate(5, (starI) {
                                            return Icon(
                                              starI < rev.score
                                                  ? Icons.star_rounded
                                                  : Icons.star_outline_rounded,
                                              size: 13,
                                              color: starGold,
                                            );
                                          }),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      rev.comment,
                                      style: const TextStyle(
                                        fontSize: 12.5,
                                        color: Color(0xFF334155),
                                        height: 1.35,
                                      ),
                                    ),
                                    if (rev.createdAt.isNotEmpty) ...[
                                      const SizedBox(height: 4),
                                      Text(
                                        rev.createdAt,
                                        style: const TextStyle(
                                          fontSize: 11,
                                          color: Color(0xFF94A3B8),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ],
                          );
                        },
                      ),
              ),

              // Bottom Sticky "เขียนรีวิว" Action
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border(
                    top: BorderSide(color: Colors.grey.withOpacity(0.2)),
                  ),
                ),
                child: SizedBox(
                  width: double.infinity,
                  height: 44,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(sheetCtx);
                      _openWriteReviewDialog(context, product);
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: crimsonRed,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    icon: const Icon(Icons.rate_review_outlined, size: 18),
                    label: const Text(
                      'เขียนรีวิวสำหรับสินค้ารุ่นนี้',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final productProvider = context.watch<ProductProvider>();
    final filteredProducts = productProvider.filteredProducts;
    final categories = ['ทั้งหมด', 'Predator', 'F50', 'Copa'];

    final screenWidth = MediaQuery.of(context).size.width;
    final isTabletOrWeb = screenWidth >= 768;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: crimsonRed,
        elevation: 0,
        centerTitle: true,
        title: const Text(
          'รีวิวสินค้า (Product Reviews)',
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
            fontSize: 19,
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await Future.wait([
            context.read<ProductProvider>().loadProducts(),
            context.read<ProductProvider>().loadReviews(),
          ]);
        },
        color: crimsonRed,
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 960),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 14),

                  // 1. Series Filter Chips (ทั้งหมด, Predator, F50, Copa)
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
                  const SizedBox(height: 14),

                  // 2. Summary & Rating KPI Banner
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.verified_outlined,
                          color: crimsonRed,
                          size: 18,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'คะแนนรีวิวสินค้า (${filteredProducts.length} รุ่นสตั๊ด)',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF334155),
                            ),
                          ),
                        ),
                        Row(
                          children: const [
                            Icon(Icons.star_rounded, color: starGold, size: 16),
                            SizedBox(width: 3),
                            Text(
                              '4.9 / 5.0 (รีวิวจริง)',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFFB45309),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),

                  // 3. Products List / Grid with Review Cards
                  Expanded(
                    child: productProvider.isLoading
                        ? const Center(
                            child: CircularProgressIndicator(color: crimsonRed),
                          )
                        : filteredProducts.isEmpty
                            ? const Center(
                                child: Text(
                                  'ไม่พบรายการรีวิวสำหรับหมวดหมู่นี้',
                                  style: TextStyle(
                                    fontSize: 16,
                                    color: Colors.grey,
                                  ),
                                ),
                              )
                            : isTabletOrWeb
                                ? GridView.builder(
                                    padding: const EdgeInsets.only(bottom: 24),
                                    itemCount: filteredProducts.length,
                                    gridDelegate:
                                        const SliverGridDelegateWithFixedCrossAxisCount(
                                      crossAxisCount: 2,
                                      crossAxisSpacing: 14,
                                      mainAxisSpacing: 14,
                                      childAspectRatio: 1.15,
                                    ),
                                    itemBuilder: (context, index) {
                                      final product = filteredProducts[index];
                                      final ratingAvg = productProvider
                                          .getProductRating(product.id);
                                      final reviewCount = productProvider
                                          .getProductReviewCount(product.id);
                                      final latestReview = productProvider
                                          .getLatestReviewForProduct(product.id);

                                      return ReviewCard(
                                        product: product,
                                        ratingAvg: ratingAvg,
                                        reviewCount: reviewCount,
                                        latestReview: latestReview,
                                        onViewAllReviews: () =>
                                            _openAllReviewsModal(context, product),
                                        onWriteReview: () =>
                                            _openWriteReviewDialog(context, product),
                                      );
                                    },
                                  )
                                : ListView.builder(
                                    padding: const EdgeInsets.only(bottom: 24),
                                    itemCount: filteredProducts.length,
                                    itemBuilder: (context, index) {
                                      final product = filteredProducts[index];
                                      final ratingAvg = productProvider
                                          .getProductRating(product.id);
                                      final reviewCount = productProvider
                                          .getProductReviewCount(product.id);
                                      final latestReview = productProvider
                                          .getLatestReviewForProduct(product.id);

                                      return ReviewCard(
                                        product: product,
                                        ratingAvg: ratingAvg,
                                        reviewCount: reviewCount,
                                        latestReview: latestReview,
                                        onViewAllReviews: () =>
                                            _openAllReviewsModal(context, product),
                                        onWriteReview: () =>
                                            _openWriteReviewDialog(context, product),
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
