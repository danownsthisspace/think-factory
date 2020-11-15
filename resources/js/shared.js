export default {
    getUserId: async function() {
        let userId = localStorage.getItem("userId");
        if (userId === null) {
            const response = await axios.post("/api/user/create");
            userId = response.data;
            localStorage.setItem("userId", userId);
        }
        return userId
    }
};
