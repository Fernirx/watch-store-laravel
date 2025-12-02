import { useState, useEffect } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import productService from '../../services/productService';

const ProductList = () => {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchParams, setSearchParams] = useSearchParams();

  const selectedCategory = searchParams.get('category');
  const selectedBrand = searchParams.get('brand');
  const searchQuery = searchParams.get('search');

  useEffect(() => {
    fetchData();
  }, [selectedCategory, selectedBrand, searchQuery]);

  const fetchData = async () => {
    try {
      setLoading(true);
      const params = {};
      if (selectedCategory) params.category_id = selectedCategory;
      if (selectedBrand) params.brand_id = selectedBrand;
      if (searchQuery) params.search = searchQuery;

      const [productsData, categoriesData, brandsData] = await Promise.all([
        productService.getProducts(params),
        productService.getCategories(),
        productService.getBrands(),
      ]);

      setProducts(productsData.data || []);
      setCategories(categoriesData.data || []);
      setBrands(brandsData.data || []);
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCategoryFilter = (categoryId) => {
    const params = new URLSearchParams(searchParams);
    if (categoryId) {
      params.set('category', categoryId);
    } else {
      params.delete('category');
    }
    setSearchParams(params);
  };

  const handleBrandFilter = (brandId) => {
    const params = new URLSearchParams(searchParams);
    if (brandId) {
      params.set('brand', brandId);
    } else {
      params.delete('brand');
    }
    setSearchParams(params);
  };

  return (
    <div className="product-list-page">
      <div className="container">
        <h1>Sản Phẩm</h1>

        <div className="products-layout">
          {/* Sidebar Filters */}
          <aside className="filters-sidebar">
            <div className="filter-section">
              <h3>Danh Mục</h3>
              <ul className="filter-list">
                <li>
                  <button
                    className={!selectedCategory ? 'active' : ''}
                    onClick={() => handleCategoryFilter(null)}
                  >
                    Tất cả
                  </button>
                </li>
                {categories.map((category) => (
                  <li key={category.id}>
                    <button
                      className={selectedCategory == category.id ? 'active' : ''}
                      onClick={() => handleCategoryFilter(category.id)}
                    >
                      {category.name}
                    </button>
                  </li>
                ))}
              </ul>
            </div>

            <div className="filter-section">
              <h3>Thương Hiệu</h3>
              <ul className="filter-list">
                <li>
                  <button
                    className={!selectedBrand ? 'active' : ''}
                    onClick={() => handleBrandFilter(null)}
                  >
                    Tất cả
                  </button>
                </li>
                {brands.map((brand) => (
                  <li key={brand.id}>
                    <button
                      className={selectedBrand == brand.id ? 'active' : ''}
                      onClick={() => handleBrandFilter(brand.id)}
                    >
                      {brand.name}
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          </aside>

          {/* Products Grid */}
          <div className="products-content">
            {loading ? (
              <div className="loading">Đang tải sản phẩm...</div>
            ) : products.length === 0 ? (
              <div className="no-products">Không tìm thấy sản phẩm nào</div>
            ) : (
              <div className="products-grid">
                {products.map((product) => (
                  <div key={product.id} className="product-card">
                    <Link to={`/products/${product.id}`}>
                      <div className="product-image">
                        <img
                          src={product.images?.[0]?.image_url || '/placeholder.jpg'}
                          alt={product.name}
                        />
                        {product.sale_price && (
                          <span className="sale-badge">Sale</span>
                        )}
                        {product.stock === 0 && (
                          <span className="out-of-stock-badge">Hết hàng</span>
                        )}
                      </div>
                      <div className="product-info">
                        <h3>{product.name}</h3>
                        <p className="brand">{product.brand?.name}</p>
                        <div className="price">
                          {product.sale_price ? (
                            <>
                              <span className="sale-price">
                                {parseFloat(product.sale_price).toLocaleString('vi-VN')}đ
                              </span>
                              <span className="original-price">
                                {parseFloat(product.price).toLocaleString('vi-VN')}đ
                              </span>
                            </>
                          ) : (
                            <span className="current-price">
                              {parseFloat(product.price).toLocaleString('vi-VN')}đ
                            </span>
                          )}
                        </div>
                      </div>
                    </Link>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductList;
