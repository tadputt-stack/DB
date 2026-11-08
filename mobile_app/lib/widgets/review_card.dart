import 'package:flutter/material.dart';
import '../models/product.dart';
import '../models/product_review.dart';
import '../screens/product_detail_screen.dart';

class ReviewCard extends StatelessWidget {
  final Product product;
  final double ratingAvg;
  final int reviewCount;
  final ProductReview? latestReview;
  final VoidCallback onViewAllReviews;
  final VoidCallback onWriteReview;

  const ReviewCard({
    Key? key,
    required this.product,
    required this.ratingAvg,
    required this.reviewCount,
    required this.latestReview,
    required this.onViewAllReviews,
    required this.onWriteReview,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    const crimsonRed = Color(0xFFD32F2F);
    const starGold = Color(0xFFF59E0B);

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.black.withOpacity(0.08), width: 1),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Product Summary Row (Image, Name, Series, Price, Overall Rating)
          InkWell(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => ProductDetailScreen(product: product),
                ),
              );
            },
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Product Thumbnail with Ground Type badge
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          width: 86,
                          height: 86,
                          color: const Color(0xFFF1F5F9),
                          child: Image.network(
                            product.imageUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) =>
                                const Center(
                              child: Icon(
                                Icons.sports_soccer,
                                size: 36,
                                color: Colors.grey,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Positioned(
                        bottom: 4,
                        left: 4,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 5, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.75),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            product.groundType,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 9,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 14),

                  // Product Details & Rating Summary
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Series Pill Tag
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 7, vertical: 2),
                          decoration: BoxDecoration(
                            color: crimsonRed.withOpacity(0.08),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            product.seriesName.toUpperCase(),
                            style: const TextStyle(
                              color: crimsonRed,
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        const SizedBox(height: 5),

                        // Title
                        Text(
                          product.name,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1E293B),
                            height: 1.25,
                          ),
                        ),
                        const SizedBox(height: 6),

                        // Price & Overall Rating Summary
                        Row(
                          children: [
                            Text(
                              '฿${product.price.toStringAsFixed(0)}',
                              style: const TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w800,
                                color: crimsonRed,
                              ),
                            ),
                            const Spacer(),
                            // Star Rating Badge
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.star_rounded,
                                    size: 14,
                                    color: starGold,
                                  ),
                                  const SizedBox(width: 3),
                                  Text(
                                    ratingAvg.toStringAsFixed(1),
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xFF92400E),
                                    ),
                                  ),
                                  Text(
                                    ' ($reviewCount)',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: Color(0xFFB45309),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 2. User Review Snippet (Customer feedback, reviewer name, stars, date)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14),
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: latestReview != null
                  ? Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            // User Avatar
                            CircleAvatar(
                              radius: 13,
                              backgroundColor: crimsonRed.withOpacity(0.12),
                              child: Text(
                                latestReview!.reviewerName.isNotEmpty
                                    ? latestReview!.reviewerName[0].toUpperCase()
                                    : 'U',
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: crimsonRed,
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),

                            // Reviewer Name & Verified
                            Expanded(
                              child: Row(
                                children: [
                                  Flexible(
                                    child: Text(
                                      latestReview!.reviewerName,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontSize: 12.5,
                                        fontWeight: FontWeight.w600,
                                        color: Color(0xFF334155),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 4),
                                  const Icon(
                                    Icons.verified,
                                    size: 13,
                                    color: Color(0xFF2563EB),
                                  ),
                                ],
                              ),
                            ),

                            // Star Icons for this specific review
                            Row(
                              children: List.generate(5, (starIdx) {
                                return Icon(
                                  starIdx < latestReview!.score
                                      ? Icons.star_rounded
                                      : Icons.star_outline_rounded,
                                  size: 13,
                                  color: starGold,
                                );
                              }),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),

                        // Review Comment
                        Text(
                          latestReview!.comment.isNotEmpty
                              ? latestReview!.comment
                              : 'รองเท้าคุณภาพดี สวมใส่กระชับ เตะบอลสนุกมาก คุ้มค่าคุ้มราคา',
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 12.5,
                            color: Color(0xFF475569),
                            height: 1.35,
                          ),
                        ),

                        if (latestReview!.createdAt.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Text(
                            latestReview!.createdAt,
                            style: const TextStyle(
                              fontSize: 10.5,
                              color: Color(0xFF94A3B8),
                            ),
                          ),
                        ],
                      ],
                    )
                  : const Center(
                      child: Padding(
                        padding: EdgeInsets.symmetric(vertical: 8),
                        child: Text(
                          'ยังไม่มีรีวิวสำหรับสินค้านี้ เป็นคนแรกที่รีวิว!',
                          style: TextStyle(
                            fontSize: 12,
                            color: Color(0xFF94A3B8),
                            fontStyle: FontStyle.italic,
                          ),
                        ),
                      ),
                    ),
            ),
          ),
          const SizedBox(height: 12),

          // 3. Action Buttons (View All Reviews & Write a Review)
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
            child: Row(
              children: [
                // "ดูรีวิวทั้งหมด" Button
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: onViewAllReviews,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF475569),
                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 9),
                      visualDensity: VisualDensity.compact,
                    ),
                    icon: const Icon(Icons.chat_bubble_outline_rounded, size: 14),
                    label: const Text(
                      'ดูรีวิวทั้งหมด',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),

                // "เขียนรีวิว" Button
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: onWriteReview,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: crimsonRed,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 9),
                      visualDensity: VisualDensity.compact,
                    ),
                    icon: const Icon(Icons.rate_review_outlined, size: 14),
                    label: const Text(
                      'เขียนรีวิว',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

