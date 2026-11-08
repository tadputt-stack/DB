class ProductReview {
  final int ratingId;
  final int productId;
  final String productName;
  final String productImageUrl;
  final String reviewerName;
  final int score;
  final String comment;
  final String createdAt;

  ProductReview({
    required this.ratingId,
    required this.productId,
    this.productName = '',
    this.productImageUrl = '',
    required this.reviewerName,
    required this.score,
    required this.comment,
    required this.createdAt,
  });

  factory ProductReview.fromJson(Map<String, dynamic> json) {
    return ProductReview(
      ratingId: int.tryParse('${json['rating_id'] ?? json['id'] ?? 0}') ?? 0,
      productId: int.tryParse('${json['product_id'] ?? 0}') ?? 0,
      productName: json['product_name']?.toString() ?? '',
      productImageUrl:
          (json['imageUrl'] ?? json['image_url'] ?? json['product_image'] ?? '')
              .toString(),
      reviewerName: json['reviewer_name']?.toString() ??
          json['display_reviewer']?.toString() ??
          'ผู้ใช้งาน OnlySport',
      score: int.tryParse('${json['score'] ?? 5}') ?? 5,
      comment: json['comment']?.toString() ?? '',
      createdAt: json['created_at']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'rating_id': ratingId,
      'product_id': productId,
      'product_name': productName,
      'imageUrl': productImageUrl,
      'reviewer_name': reviewerName,
      'score': score,
      'comment': comment,
      'created_at': createdAt,
    };
  }
}

