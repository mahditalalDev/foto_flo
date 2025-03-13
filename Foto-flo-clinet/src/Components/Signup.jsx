import { useState } from "react";
import Header from "./header/Header.jsx";
import { Link } from "react-router-dom";
import BASE_URL from "../Services/baseAPI.js";
import { useSignup } from "./Hooks/useSignup.js";

const Signup = () => {
  const { signupUser } = useSignup();

  const [formData, setFormData] = useState({
    full_name: "",
    email: "",
    password: "",
    confirmPassword: "",
  });
  const baseapi = BASE_URL;
  console.log(baseapi);
  const handleChange = (e) => {
    const { name, value } = e.target;

    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }));
  };
  const validate = (data) => {
    const { full_name, email, password, confirmPassword } = data;

    // Check if any field is empty
    if (!full_name || !email || !password || !confirmPassword) {
      return false;
    }

    // Check if passwords match
    if (password !== confirmPassword) {
      return false;
    }

    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (validate(formData)) {
      signupUser(formData);
      // todo: register new user with axios
    }
  };

  return (
    <div className="container">
      <Header />
      <div className="auth-container">
        <h2 className="auth-title">Create Account</h2>
        <form className="auth-form">
          <input
            // type="text"
            className="auth-input"
            placeholder="Full Name"
            name="full_name"
            value={formData.fullName}
            onChange={handleChange}
          />
          <input
            // type="email"
            className="auth-input"
            placeholder="Email"
            name="email"
            value={formData.email}
            onChange={handleChange}
          />
          <input
            // type="password"
            className="auth-input"
            placeholder="Password"
            name="password"
            value={formData.password}
            onChange={handleChange}
          />
          <input
            // type="password"
            className="auth-input"
            placeholder="Confirm Password"
            name="confirmPassword"
            value={formData.confirmPassword}
            onChange={handleChange}
          />
          <button className="auth-btn" onClick={handleSubmit}>
            Sign Up
          </button>
        </form>
        <div className="auth-links">
          <Link to="/login" className="auth-link">
            Already have an account? Login{" "}
          </Link>
        </div>
      </div>
    </div>
  );
};

export default Signup;
