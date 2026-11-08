import 'package:flutter/material.dart';
import '../models/product.dart';
import '../models/product_review.dart';
import '../services/api_service.dart';

class ProductProvider with ChangeNotifier {
  List<Product> _products = [];
  List<ProductReview> _reviews = [];
  bool _isLoading = false;
  bool _isLoadingReviews = false;
  String _selectedCategory = 'ทั้งหมด';

  List<Product> get products => _products;
  List<ProductReview> get reviews => _reviews;
  bool get isLoading => _isLoading;
  bool get isLoadingReviews => _isLoadingReviews;
  String get selectedCategory => _selectedCategory;

  ProductProvider() {
    loadProducts();
    loadReviews();
  }

  Future<void> loadProducts() async {
    _isLoading = true;
    notifyListeners();

    try {
      final fetched = await ApiService.fetchProducts();
      _products = List<Product>.from(fetched);
      debugPrint('[ProductProvider] Successfully populated ${_products.length} products to state');
    } catch (e, stack) {
      debugPrint('[ProductProvider] Error loading products: $e');
      debugPrint('[ProductProvider] Stack trace: $stack');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadReviews({int? productId}) async {
    _isLoadingReviews = true;
    notifyListeners();

    try {
      final fetched = await ApiService.fetchReviews(productId: productId);
      _reviews = List<ProductReview>.from(fetched);
      debugPrint('[ProductProvider] Loaded ${_reviews.length} reviews');
    } catch (e) {
      debugPrint('[ProductProvider] Error loading reviews: $e');
    } finally {
      _isLoadingReviews = false;
      notifyListeners();
    }
  }

  List<ProductReview> getReviewsForProduct(int productId) {
    return _reviews.where((r) => r.productId == productId).toList();
  }

  ProductReview? getLatestReviewForProduct(int productId) {
    final list = getReviewsForProduct(productId);
    if (list.isNotEmpty) return list.first;
    if (_reviews.isNotEmpty) {
      final fallbackIndex = (productId.abs() - 1) % _reviews.length;
      return _reviews[fallbackIndex];
    }
    return null;
  }

  double getProductRating(int productId) {
    final list = getReviewsForProduct(productId);
    if (list.isNotEmpty) {
      final sum = list.fold<int>(0, (prev, r) => prev + r.score);
      return double.parse((sum / list.length).toStringAsFixed(1));
    }
    final prod = _products.firstWhere(
      (p) => p.id == productId,
      orElse: () => Product(
        id: 0,
        name: '',
        imageUrl: '',
        price: 0,
        seriesId: 1,
        seriesName: '',
        groundType: '',
        description: '',
      ),
    );
    return prod.ratingAvg;
  }

  int getProductReviewCount(int productId) {
    final list = getReviewsForProduct(productId);
    if (list.isNotEmpty) return list.length;
    final prod = _products.firstWhere(
      (p) => p.id == productId,
      orElse: () => Product(
        id: 0,
        name: '',
        imageUrl: '',
        price: 0,
        seriesId: 1,
        seriesName: '',
        groundType: '',
        description: '',
      ),
    );
    return prod.ratingCount > 0 ? prod.ratingCount : 1;
  }

  Future<bool> submitReview({
    required int productId,
    required String reviewerName,
    required int score,
    required String comment,
  }) async {
    final success = await ApiService.submitReview(
      productId: productId,
      reviewerName: reviewerName,
      score: score,
      comment: comment,
    );

    if (success) {
      _reviews.insert(
        0,
        ProductReview(
          ratingId: DateTime.now().millisecondsSinceEpoch,
          productId: productId,
          reviewerName: reviewerName.trim().isEmpty ? 'คุณ (ผู้ใช้งาน)' : reviewerName.trim(),
          score: score,
          comment: comment.trim(),
          createdAt: 'เมื่อสักครู่',
        ),
      );
      notifyListeners();
    }
    return success;
  }

  void setSelectedCategory(String category) {
    _selectedCategory = category;
    notifyListeners();
  }

  List<Product> get filteredProducts {
    final cat = _selectedCategory.trim();
    if (cat.isEmpty || cat == 'ทั้งหมด' || cat.toLowerCase() == 'all') {
      return _products;
    }

    int targetSeriesId = 0;
    if (cat.toLowerCase() == 'predator') {
      targetSeriesId = 1;
    } else if (cat.toLowerCase() == 'f50') {
      targetSeriesId = 2;
    } else if (cat.toLowerCase() == 'copa') {
      targetSeriesId = 3;
    }

    return _products.where((product) {
      if (product.seriesName.toLowerCase() == cat.toLowerCase()) {
        return true;
      }
      if (targetSeriesId > 0 && product.seriesId == targetSeriesId) {
        return true;
      }
      return product.name.toLowerCase().contains(cat.toLowerCase());
    }).toList();
  }

  List<Product> get recommendedProducts {
    return _products.take(4).toList();
  }

  void toggleFavorite(int productId) {
    final index = _products.indexWhere((p) => p.id == productId);
    if (index != -1) {
      _products[index].isFavorite = !_products[index].isFavorite;
      notifyListeners();
    }
  }
}
