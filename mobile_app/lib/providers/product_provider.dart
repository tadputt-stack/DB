import 'package:flutter/material.dart';
import '../models/product.dart';
import '../services/api_service.dart';

class ProductProvider with ChangeNotifier {
  List<Product> _products = [];
  bool _isLoading = false;
  String _selectedCategory = 'ทั้งหมด';

  List<Product> get products => _products;
  bool get isLoading => _isLoading;
  String get selectedCategory => _selectedCategory;

  ProductProvider() {
    loadProducts();
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
