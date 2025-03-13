import baseApi from "./baseAPI";

export const Signup = async ({ full_name,email, password }) => {
  try {
    const response = await baseApi.post(
      `/AuthController.php?action=login`,
      {full_name, email, password }
    );

    return response;
  } catch (error) {
    console.error("Error renaming file/folder:", error);
    throw error;
  }
};
