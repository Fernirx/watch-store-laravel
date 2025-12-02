import { useState, useEffect } from 'react';
import axios from '../../api/axiosConfig';

const Dashboard = () => {
  const [stats, setStats] = useState({
    totalProducts: 0,
    totalCategories: 0,
    totalBrands: 0,
    totalOrders: 0,
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    try {
      setLoading(true);
      const [products, categories, brands, orders] = await Promise.all([
        axios.get('/products?per_page=1'),
        axios.get('/categories'),
        axios.get('/brands'),
        axios.get('/orders'),
      ]);

      setStats({
        totalProducts: products.data.data.total || 0,
        totalCategories: categories.data.data?.length || 0,
        totalBrands: brands.data.data?.length || 0,
        totalOrders: orders.data.data?.length || 0,
      });
    } catch (error) {
      console.error('Error fetching stats:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="loading">Loading dashboard...</div>;
  }

  return (
    <div className="admin-dashboard">
      <h1>Dashboard</h1>

      <div className="stats-grid">
        <div className="stat-card">
          <h3>Products</h3>
          <p className="stat-number">{stats.totalProducts}</p>
        </div>

        <div className="stat-card">
          <h3>Categories</h3>
          <p className="stat-number">{stats.totalCategories}</p>
        </div>

        <div className="stat-card">
          <h3>Brands</h3>
          <p className="stat-number">{stats.totalBrands}</p>
        </div>

        <div className="stat-card">
          <h3>Orders</h3>
          <p className="stat-number">{stats.totalOrders}</p>
        </div>
      </div>

      <div className="quick-actions">
        <h2>Quick Actions</h2>
        <div className="actions-grid">
          <a href="/admin/products/create" className="action-btn">
            Add New Product
          </a>
          <a href="/admin/categories/create" className="action-btn">
            Add New Category
          </a>
          <a href="/admin/brands/create" className="action-btn">
            Add New Brand
          </a>
          <a href="/admin/orders" className="action-btn">
            View All Orders
          </a>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
