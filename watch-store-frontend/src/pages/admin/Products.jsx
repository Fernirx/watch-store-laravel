import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import productService from '../../services/productService';

const Products = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [search, setSearch] = useState('');

  useEffect(() => {
    fetchProducts();
  }, [currentPage, search]);

  const fetchProducts = async () => {
    try {
      setLoading(true);
      const response = await productService.getProducts({
        page: currentPage,
        per_page: 20,
        search: search || undefined,
      });

      setProducts(response.data.data || []);
      setTotalPages(response.data.last_page || 1);
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
      return;
    }

    try {
      await productService.deleteProduct(id);
      fetchProducts();
    } catch (error) {
      console.error('Error deleting product:', error);
      alert('Không thể xóa sản phẩm');
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    setCurrentPage(1);
    fetchProducts();
  };

  if (loading) {
    return <div className="loading">Đang tải...</div>;
  }

  return (
    <div className="admin-products">
      <div className="admin-header">
        <h1>Quản Lý Sản Phẩm</h1>
        <Link to="/admin/products/create" className="btn-primary">
          Thêm Sản Phẩm Mới
        </Link>
      </div>

      <div className="admin-filters">
        <form onSubmit={handleSearch} className="search-form">
          <input
            type="text"
            placeholder="Tìm kiếm sản phẩm..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="search-input"
          />
          <button type="submit" className="btn-search">
            Tìm kiếm
          </button>
        </form>
      </div>

      <div className="admin-table-container">
        <table className="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Hình ảnh</th>
              <th>Tên sản phẩm</th>
              <th>Danh mục</th>
              <th>Thương hiệu</th>
              <th>Giá</th>
              <th>Giá KM</th>
              <th>Tồn kho</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            {products.length === 0 ? (
              <tr>
                <td colSpan="10" className="text-center">
                  Không có sản phẩm nào
                </td>
              </tr>
            ) : (
              products.map((product) => (
                <tr key={product.id}>
                  <td>{product.id}</td>
                  <td>
                    {product.image_url && (
                      <img
                        src={product.image_url}
                        alt={product.name}
                        className="table-image"
                      />
                    )}
                  </td>
                  <td>{product.name}</td>
                  <td>{product.category?.name || '-'}</td>
                  <td>{product.brand?.name || '-'}</td>
                  <td>{product.price.toLocaleString('vi-VN')} ₫</td>
                  <td>
                    {product.sale_price
                      ? `${product.sale_price.toLocaleString('vi-VN')} ₫`
                      : '-'}
                  </td>
                  <td>
                    <span
                      className={
                        product.stock_quantity === 0
                          ? 'badge badge-danger'
                          : product.stock_quantity <= 5
                          ? 'badge badge-warning'
                          : 'badge badge-success'
                      }
                    >
                      {product.stock_quantity}
                    </span>
                  </td>
                  <td>
                    <span
                      className={
                        product.is_active
                          ? 'badge badge-success'
                          : 'badge badge-secondary'
                      }
                    >
                      {product.is_active ? 'Hoạt động' : 'Ngừng'}
                    </span>
                  </td>
                  <td>
                    <div className="action-buttons">
                      <Link
                        to={`/admin/products/edit/${product.id}`}
                        className="btn-icon btn-edit"
                      >
                        Sửa
                      </Link>
                      <button
                        onClick={() => handleDelete(product.id)}
                        className="btn-icon btn-delete"
                      >
                        Xóa
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>

      {totalPages > 1 && (
        <div className="pagination">
          <button
            onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
            disabled={currentPage === 1}
            className="btn-pagination"
          >
            Trước
          </button>
          <span className="page-info">
            Trang {currentPage} / {totalPages}
          </span>
          <button
            onClick={() => setCurrentPage((p) => Math.min(totalPages, p + 1))}
            disabled={currentPage === totalPages}
            className="btn-pagination"
          >
            Sau
          </button>
        </div>
      )}
    </div>
  );
};

export default Products;
