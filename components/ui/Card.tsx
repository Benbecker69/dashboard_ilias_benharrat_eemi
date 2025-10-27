import { ReactNode } from "react";
import { LucideIcon } from "lucide-react";

interface CardProps {
  children: ReactNode;
  className?: string;
  noOverflow?: boolean;
}

interface CardHeaderProps {
  title: ReactNode;
  icon?: LucideIcon;
  action?: ReactNode;
  gradient?: boolean;
}

interface CardBodyProps {
  children: ReactNode;
  noPadding?: boolean;
  className?: string;
}

export const Card = ({
  children,
  className = "",
  noOverflow = false,
}: CardProps) => {
  return (
    <div
      className={`bg-white rounded-xl shadow-sm border border-gray-200 ${
        noOverflow ? "" : "overflow-hidden"
      } ${className}`}
    >
      {children}
    </div>
  );
};

export const CardHeader = ({
  title,
  icon: Icon,
  action,
  gradient = false,
}: CardHeaderProps) => {
  const baseClasses = "px-6 py-4 flex items-center justify-between";
  const bgClasses = gradient
    ? "bg-gradient-to-r from-gray-50 to-gray-100"
    : "bg-gray-50";

  const isStringTitle = typeof title === "string";

  return (
    <div className={`${baseClasses} ${bgClasses}`}>
      <h3
        className={`text-lg font-display font-semibold text-gray-900 ${
          isStringTitle && Icon ? "flex items-center" : ""
        }`}
      >
        {isStringTitle && Icon && (
          <Icon className="h-5 w-5 mr-2 text-gray-600" />
        )}
        {title}
      </h3>
      {action && <div>{action}</div>}
    </div>
  );
};

export const CardBody = ({
  children,
  noPadding = false,
  className = "",
}: CardBodyProps) => {
  const defaultPadding = noPadding ? "" : "p-6";
  return (
    <div className={`${defaultPadding} ${className}`.trim()}>{children}</div>
  );
};
