import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import Header from "./header/Header";
import SearchBar from "./header/SearchBar";
import AddImage from "./header/Dialogs/AddImage";

const HomePage = () => {
  const navigate = useNavigate();
  const [isDialogOpen, setToggleDialog] = useState(false);
  const [photos, setPhotos] = useState([]);

  useEffect(() => {
    const token = localStorage.getItem("user_token");
    // if (!token) {
    //   navigate("/signup");
    //   return;
    // }

    const savedPhotos = localStorage.getItem("photos");
    if (savedPhotos) {
      setPhotos(JSON.parse(savedPhotos));
    }
  }, [navigate]);

  useEffect(() => {
    localStorage.setItem("photos", JSON.stringify(photos));
  }, [photos]);


  const handleEditPhoto = (updatedPhoto) => {
    setPhotos(photos.map(photo => 
      photo.id === updatedPhoto.id ? updatedPhoto : photo
    ));
    setToggleDialog(false);
  };
  const handleSavePhoto = (newPhoto) => {
    const updatedPhotos = [...photos, newPhoto];
    setPhotos(updatedPhotos);
    
    // Save to localStorage
    localStorage.setItem("photos", JSON.stringify(updatedPhotos));
  };

  const handleDeletePhoto = (photoId) => {
    setPhotos(photos.filter(photo => photo.id !== photoId));
  };

  return (
    <div>
      <Header />
      <SearchBar
        isDialogOpen={isDialogOpen}
        setToggleDialog={setToggleDialog}
      />
      <AddImage 
        isDialogOpen={isDialogOpen}
        setToggleDialog={setToggleDialog}
        onSave={handleSavePhoto}
      />

      <div className="photo-gallery">
        {photos.length > 0 ? (
          photos.map((photo) => (
            <div key={photo.id} className="photo-card">
              <img
                src={photo.imageUrl}
                className="photo-img"
                alt={photo.title}
              />
              <div className="photo-info">
                <h3 className="photo-title">{photo.title}</h3>
                <p className="photo-description">{photo.description}</p>
              </div>
              <div className="actions">
                <button
                  className="action-btn"
                  onClick={() => setToggleDialog(true)}
                >
                  ✏️ Edit
                </button>
                <button
                  className="action-btn"
                  onClick={() => handleDeletePhoto(photo.id)}
                >
                  🗑️ Delete
                </button>
              </div>
            </div>
          ))
        ) : (
          <p>No photos available.</p>
        )}
      </div>
    </div>
  );
};

export default HomePage;