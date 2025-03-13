import { Login } from "../../Services/loginApi";

export const useLogin = () => {

  const loginUser= async (email,password) => {
    try {
      const result = await Login({ email,password: password });
      console.log("login successfully:", result);
      return result;
    } catch (err) {
      
      console.error("Rename Error:", err);
    } 
  };

  return { loginUser };
};
