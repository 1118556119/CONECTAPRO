/* filepath: c:\xampp\htdocs\CONECTAPRO\resources\js\utils\colors.js */
/**
 * Funciones de utilidad para trabajar con los colores del sistema
 */

/**
 * Obtiene el color principal basado en el tipo de usuario
 * @param {string} userType - El tipo de usuario ('client' o 'technician')
 * @returns {string} El código de color correspondiente
 */
export const getPrimaryColor = (userType) => {
  return userType === 'technician' ? '#01A66F' : '#0B4F6C';
};

/**
 * Obtiene el color de acento basado en el tipo de usuario
 * @param {string} userType - El tipo de usuario ('client' o 'technician')
 * @returns {string} El código de color correspondiente
 */
export const getAccentColor = (userType) => {
  return userType === 'technician' ? '#00B2CA' : '#FF6B35';
};

/**
 * Genera un gradiente CSS para el tipo de usuario
 * @param {string} userType - El tipo de usuario ('client' o 'technician')
 * @returns {string} La cadena CSS para el gradiente
 */
export const getGradient = (userType) => {
  if (userType === 'technician') {
    return 'linear-gradient(135deg, #01A66F 0%, #018d5a 100%)';
  }
  return 'linear-gradient(135deg, #0B4F6C 0%, #083d56 100%)';
};

/**
 * Genera una clase CSS basada en el tipo de usuario
 * @param {string} userType - El tipo de usuario ('client' o 'technician')
 * @param {string} baseClass - La clase base a la que añadir el modificador
 * @returns {string} Clase CSS completa
 */
export const getUserClass = (userType, baseClass) => {
  return `${baseClass} ${baseClass}-${userType || 'client'}`;
};