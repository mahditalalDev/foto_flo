import baseApi from "./baseAPI";

export const Login = async ({ email, password }) => {
  try {
    const response = await baseApi.post(
      `/AuthController.php?action=login`,
      { email, password }
    );

    return response;
  } catch (error) {
    console.error("Error renaming file/folder:", error);
    throw error;
  }
};
