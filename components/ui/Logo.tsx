import React from 'react';

interface LogoProps {
    className?: string;
    showText?: boolean;
}

export const Logo: React.FC<LogoProps> = ({ className = "", showText = true }) => {
    return (
        <div className={`flex items-center ${className}`}>
            {showText && (
                <div className="flex items-center space-x-3">
                    <div className="h-10 w-10 rounded-lg bg-gradient-to-br from-teal-500 to-blue-600 flex items-center justify-center">
                        <span className="text-white font-bold text-xl">D</span>
                    </div>
                    <span className="text-2xl font-display font-bold bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">
                        Dashboard
                    </span>
                </div>
            )}
        </div>
    );
};
