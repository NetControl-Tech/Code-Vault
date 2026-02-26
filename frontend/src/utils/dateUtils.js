/**
 * Date formatting utilities for consistent date display across the application.
 * Uses Gregorian calendar format (D/M/YYYY) as per project requirements.
 */

/**
 * Format a date string to Gregorian format (D/M/YYYY)
 * @param {string|null} dateStr - The date string to format (e.g., "2024-02-08")
 * @returns {string} Formatted date string or '-' if empty
 */
export function formatDate(dateStr) {
    if (!dateStr) return '-';
    
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return '-';
        
        // Use en-GB locale for D/M/YYYY format (Gregorian)
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'numeric',
            year: 'numeric'
        });
    } catch (error) {
        console.warn('Invalid date format:', dateStr);
        return '-';
    }
}

/**
 * Format a date string with time (D/M/YYYY HH:MM)
 * @param {string|null} dateStr - The datetime string to format
 * @returns {string} Formatted datetime string or '-' if empty
 */
export function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    
    try {
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return '-';
        
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        console.warn('Invalid datetime format:', dateStr);
        return '-';
    }
}

/**
 * Format a date for input fields (YYYY-MM-DD)
 * @param {string|Date|null} date - The date to format
 * @returns {string} ISO date string or empty string
 */
export function formatDateForInput(date) {
    if (!date) return '';
    
    try {
        const d = date instanceof Date ? date : new Date(date);
        if (isNaN(d.getTime())) return '';
        
        return d.toISOString().split('T')[0];
    } catch (error) {
        return '';
    }
}
