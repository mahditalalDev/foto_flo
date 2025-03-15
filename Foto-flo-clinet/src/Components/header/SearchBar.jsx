import React from "react";

const SearchBar = ({isDialogOpen,setToggleDialog}) => {
  return (
    <div className="search-filter">
      <input
        type="text"
        className="search-box"
        placeholder="Search photos..."
      />
      <div className="tools">
      <select className="tag-filter">
        <option value="">All Tags</option>
      </select>
      <button className="add-btn" onClick={()=>{
        setToggleDialog(true)
      }} > +Add</button>
      </div>
     
    </div>
  );
};

export default SearchBar;
