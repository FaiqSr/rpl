module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#F9D71C',
          50: '#FFFBEB',
          100: '#FEF3C7',
          200: '#FDE68A',
          300: '#F9D71C',
          400: '#EAB308',
          500: '#CA8A04',
        },
        dark: {
          DEFAULT: '#2F2F2F',
          50: '#F9FAFB',
          100: '#F3F4F6',
          200: '#E5E7EB',
          300: '#2F2F2F',
          400: '#1F2937',
          500: '#111827',
        },
        success: {
          DEFAULT: '#69B578',
          light: '#D1FAE5',
        },
        warning: {
          DEFAULT: '#FF9F1C',
          light: '#FEF3C7',
        },
        danger: {
          DEFAULT: '#FF4B3E',
          light: '#FEE2E2',
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      animation: {
        'float': 'float 6s ease-in-out infinite',
        'fadeIn': 'fadeIn .5s ease-in-out',
        'slideUp': 'slideUp .5s ease-in-out',
        'slideDown': 'slideDown .5s ease-in-out',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideDown: {
          '0%': { transform: 'translateY(-20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        }
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ]
}