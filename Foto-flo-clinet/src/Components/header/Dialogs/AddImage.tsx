import React, { useState } from "react";

const AddImage = ({ isDialogOpen, setToggleDialog, onSave }) => {
  const [newImageData, setNewImageData] = useState({
    title: "",
    description: "",
    tags: "",
    image: "",
  });

  const handleImageUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
      setNewImageData((prev) => ({
        ...prev,
        image: file,
      }));
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const imageUrl = URL.createObjectURL(newImageData.image);

    onSave({
      ...newImageData,
      imageUrl: imageUrl,
      tags: newImageData.tags.split(",").map((t) => t.trim()),
    });

    setToggleDialog(false);
    setNewImageData({ title: "", description: "", tags: "", image: null });
  };

  if (!isDialogOpen) return null;

  return (
    <div className="modal">
      <div className="modal-content">
        <h2>🎨 Add New Photo</h2>
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <input
              type="text"
              placeholder="Title"
              value={newImageData.title}
              onChange={(e) =>
                setNewImageData({ ...newImageData, title: e.target.value })
              }
              required
            />
          </div>
          <div className="form-group">
            <textarea
              placeholder="Description"
              value={newImageData.description}
              onChange={(e) =>
                setNewImageData({
                  ...newImageData,
                  description: e.target.value,
                })
              }
              required
            />
          </div>
          <div className="form-group">
            <input
              type="text"
              placeholder="Tags (comma separated)"
              value={newImageData.tags}
              onChange={(e) =>
                setNewImageData({ ...newImageData, tags: e.target.value })
              }
              required
            />
          </div>
          <div className="form-group">
            <input
              type="file"
              id="image"
              accept="image/*"
              required
              onChange={handleImageUpload}
            />
            {newImageData.image && (
              <img
                src={newImageData.image}
                alt="Preview"
                style={{ maxWidth: "100px", marginTop: "10px" }}
              />
            )}
          </div>
          <div>
            <button type="submit" className="add-btn">
              Save
            </button>
            <button
              type="button"
              className="action-btn"
              onClick={() => setToggleDialog(false)}
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddImage;
