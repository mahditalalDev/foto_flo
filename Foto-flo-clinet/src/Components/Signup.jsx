import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Link } from "react-router-dom";
import Header from "./header/Header.jsx";
import { validate } from "../common/validation.js";
import axios from "axios";

const Signup = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    username: "",
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

    if (!validate(formData)) {
      setError("Please fill in all fields correctly");
      return;
    }

    try {
      const response = await axios.post(
        "http://localhost/SEfactory/foto_flo/Foto-flo-server/apis/v1/AuthController.php?action=register",
        formData
      );

      if (response.data.success) {
        localStorage.setItem("user_token", response.data.data);
        navigate("/homepage");
      } else {
        setError(response.data.message || "Registration failed");
      }
    } catch (err) {
      setError(
        err.response?.data?.message || "Email or username is already registered"
      );
    }
  };

  return (
    <div className="container">
      <Header />
      {error && <div className="error-dialog">{error}</div>}
      <div className="auth-container">
        <h2 className="auth-title">Create Account</h2>
        <form className="auth-form" onSubmit={handleSubmit}>
          <input
            type="text"
            className="auth-input"
            placeholder="Full Name"
            name="username"
            value={formData.username}
            onChange={handleChange}
          />
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
            Sign Up
          </button>
        </form>
        <div className="auth-links">
          <Link to="/login" className="auth-link">
            Already have an account? Login
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Signup;
