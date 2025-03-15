import axios from "axios";
import { Link, useNavigate } from "react-router-dom";
import Header from "./header/Header";
import { useState } from "react";

const Login = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    email: "",
    password: "",
  });
  const [error, setError] = useState(null);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null); // Reset error message
    try {
      const response = await axios.post(
        "http://localhost/SEfactory/foto_flo/Foto-flo-server/apis/v1/AuthController.php?action=login",
        formData
      );

      if (response.data.success) {
        localStorage.setItem("user_token", response.data.data);
        navigate("/homepage");
      } else {
        setError(response.data.message || "Registration failed");
      }
    } catch (err) {
      setError(err.response?.data?.message || "Invalid Email or Password");
    }
  };
  return (
    <div className="container">
      <Header />
      {error && <div className="error-dialog">{error}</div>}
      <div className="auth-container">
        <h2 className="auth-title">Login</h2>
        <form className="auth-form" onSubmit={handleSubmit}>
          <input
            type="email"
            className="auth-input"
            placeholder="Email"
            name="email"
            value={formData.email}
            onChange={handleChange}
          />
          <input
            type="password"
            className="auth-input"
            placeholder="Password"
            name="password"
            value={formData.password}
            onChange={handleChange}
          />

          <button type="submit" className="auth-btn">
            Login
          </button>
        </form>
        <div className="auth-links">
          <Link to="/signup" className="auth-link">
            dont have an account? SignUp
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Login;
