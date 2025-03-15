import React, { useEffect, useState } from "react";
import SearchBar from "./SearchBar";
import { useNavigate } from "react-router-dom";

const Header = () => {
  const [token, setToken] = useState();
  const navigate = useNavigate();

  useEffect(() => {
    setToken(localStorage.getItem("user_token"));
  });
  const handleLogout = () => {
    localStorage.removeItem("user_token");
    navigate("/login");
  };

  return (
    <div className="container">
      <div className="header">
        <h1>📸 Foto Flo</h1>
        <div>
          {token && (
            <button className="logout" onClick={handleLogout}>
              logout
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default Header;
