import { Signup } from "../../Services/signupApi";

export const useSignup = () => {

  const signupUser= async (full_name,email,password) => {
    try {
      const result = await Signup({ full_name,email,password });
      console.log("signup successfully:", result);
      return result;
    } catch (err) {
      
      console.error("Rename Error:", err);
    } 
  };

  return { signupUser };
};
