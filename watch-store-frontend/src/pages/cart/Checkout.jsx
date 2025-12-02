import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useCart } from '../../contexts/CartContext';
import { useAuth } from '../../contexts/AuthContext';
import orderService from '../../services/orderService';

const Checkout = () => {
  const { cart, clearCart } = useCart();
  const { user, isAuthenticated } = useAuth();
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }

    if (!cart || cart.items?.length === 0) {
      navigate('/cart');
      return;
    }

    // Pre-fill với thông tin user
    if (user) {
      setFormData({
        customer_name: user.name || '',
        customer_email: user.email || '',
        customer_phone: user.phone || '',
      });
    }
  }, [isAuthenticated, cart, user]);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const orderData = {
        ...formData,
        items: cart.items.map((item) => ({
          product_id: item.product_id,
          quantity: item.quantity,
          price: item.product.sale_price || item.product.price,
        })),
      };

      const response = await orderService.createOrder(orderData);

      // Xóa giỏ hàng sau khi đặt hàng thành công
      await clearCart();

      // Chuyển đến trang chi tiết đơn hàng
      navigate(`/orders/${response.data.id}`, {
        state: { message: 'Đặt hàng thành công!' },
      });
    } catch (err) {
      setError(err.response?.data?.message || 'Đặt hàng thất bại');
    } finally {
      setLoading(false);
    }
  };

  if (!cart || cart.items?.length === 0) {
    return null;
  }

  const cartItems = cart.items || [];
  const subtotal = cartItems.reduce((sum, item) => {
    const price = item.product.sale_price || item.product.price;
    return sum + parseFloat(price) * item.quantity;
  }, 0);

  return (
    <div className="checkout-page">
      <div className="container">
        <h1>Thanh Toán</h1>

        {error && <div className="error-message">{error}</div>}

        <div className="checkout-layout">
          {/* Checkout Form */}
          <div className="checkout-form">
            <h2>Thông Tin Giao Hàng</h2>

            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label>Họ và tên *</label>
                <input
                  type="text"
                  name="customer_name"
                  value={formData.customer_name}
                  onChange={handleChange}
                  required
                  placeholder="Nhập họ tên"
                />
              </div>

              <div className="form-group">
                <label>Email *</label>
                <input
                  type="email"
                  name="customer_email"
                  value={formData.customer_email}
                  onChange={handleChange}
                  required
                  placeholder="Nhập email"
                />
              </div>

              <div className="form-group">
                <label>Số điện thoại</label>
                <input
                  type="tel"
                  name="customer_phone"
                  value={formData.customer_phone}
                  onChange={handleChange}
                  placeholder="Nhập số điện thoại"
                />
              </div>

              <button type="submit" className="btn-place-order" disabled={loading}>
                {loading ? 'Đang xử lý...' : 'Đặt Hàng'}
              </button>
            </form>
          </div>

          {/* Order Summary */}
          <div className="order-summary">
            <h2>Đơn Hàng Của Bạn</h2>

            <div className="summary-items">
              {cartItems.map((item) => {
                const product = item.product;
                const price = product.sale_price || product.price;

                return (
                  <div key={item.id} className="summary-item">
                    <div className="item-info">
                      <span className="item-name">{product.name}</span>
                      <span className="item-quantity">x{item.quantity}</span>
                    </div>
                    <div className="item-price">
                      {(parseFloat(price) * item.quantity).toLocaleString('vi-VN')}đ
                    </div>
                  </div>
                );
              })}
            </div>

            <div className="summary-totals">
              <div className="summary-row">
                <span>Tạm tính:</span>
                <span>{subtotal.toLocaleString('vi-VN')}đ</span>
              </div>

              <div className="summary-row total">
                <span>Tổng cộng:</span>
                <span>{subtotal.toLocaleString('vi-VN')}đ</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Checkout;
