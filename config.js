// API Configuration
const API_CONFIG = {
    BASE_URL: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' 
        ? 'http://localhost/api.php' // For local development
        : window.location.origin + '/api.php', // For production
    
    ENDPOINTS: {
        GET_ALL: '?endpoint=all',
        GET_SUBMISSIONS: '?endpoint=submissions',
        GET_LEADS: '?endpoint=leads',
        SAVE_SUBMISSION: '?endpoint=submission',
        UPDATE_LEAD: '?endpoint=lead',
        DELETE_ENTRY: '?endpoint=entry'
    },
    
    // Check if server API is available
    isServerStorageAvailable: async function() {
        try {
            const response = await fetch(this.BASE_URL + this.ENDPOINTS.GET_ALL, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                timeout: 3000 // 3 second timeout
            });
            
            return response.ok;
        } catch (error) {
            console.error('Server storage check failed:', error);
            return false;
        }
    },
    
    // Helper for making API requests
    request: async function(endpoint, method = 'GET', data = null) {
        try {
            const options = {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };
            
            if (data && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(data);
            }
            
            const response = await fetch(this.BASE_URL + endpoint, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'API request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API request error:', error);
            throw error;
        }
    }
}; 