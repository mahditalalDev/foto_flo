export const validate = (data) => {
  const { username, email, password } = data;
  if (!username || !email || !password) {
    return false;
  }
  return true;
};
