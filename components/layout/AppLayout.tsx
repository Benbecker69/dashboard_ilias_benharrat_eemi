"use client";

import { Logo } from "@/components/ui/Logo";
import {
  BellRing,
  CalendarDays,
  ChevronLeft,
  ChevronRight,
  FileCheck,
  LayoutGrid,
  MailOpen,
  Menu,
  PhoneCall,
  SearchCheck,
  ShieldCheck,
  Sunrise,
  UserPlus2,
  UsersRound,
  XCircle,
} from "lucide-react";
import { usePathname } from "next/navigation";
import React, { useState } from "react";

interface NavItem {
  name: string;
  href: string;
  icon: React.ComponentType<any>;
  badge?: number;
}

const navigation: NavItem[] = [
  {
    name: "Tableau de bord",
    href: "/dashboard",
    icon: LayoutGrid,
  },
  {
    name: "Rendez-vous",
    href: "/rendez-vous",
    icon: CalendarDays,
    badge: 3,
  },
  {
    name: "Prospects",
    href: "/prospects",
    icon: UserPlus2,
  },
  {
    name: "Clients",
    href: "/clients",
    icon: UsersRound,
  },
  {
    name: "Études scolaires",
    href: "/etudes-scolaires",
    icon: Sunrise,
  },
  {
    name: "Devis",
    href: "/devis",
    icon: FileCheck,
    badge: 5,
  },
  {
    name: "Utilisateurs",
    href: "/utilisateurs",
    icon: ShieldCheck,
  },
];

export const AppLayout: React.FC<{ children: React.ReactNode }> = ({
  children,
}) => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
  const pathname = usePathname();

  // Navigation item component
  const NavLink = ({
    item,
    isMobile = false,
  }: {
    item: NavItem;
    isMobile?: boolean;
  }) => {
    const isActive = pathname === item.href;

    const getIconColor = () => {
      switch (item.icon) {
        case LayoutGrid:
          return isActive
            ? "from-blue-500 to-indigo-600"
            : "bg-blue-100 text-blue-600";
        case CalendarDays:
          return isActive
            ? "from-green-500 to-emerald-600"
            : "bg-green-100 text-green-600";
        case UserPlus2:
          return isActive
            ? "from-purple-500 to-pink-600"
            : "bg-purple-100 text-purple-600";
        case UsersRound:
          return isActive
            ? "from-orange-500 to-red-600"
            : "bg-orange-100 text-orange-600";
        case Sunrise:
          return isActive
            ? "from-yellow-500 to-orange-600"
            : "bg-yellow-100 text-yellow-600";
        case FileCheck:
          return isActive
            ? "from-teal-500 to-cyan-600"
            : "bg-teal-100 text-teal-600";
        case ShieldCheck:
          return isActive
            ? "from-red-500 to-pink-600"
            : "bg-red-100 text-red-600";
        default:
          return isActive
            ? "from-gray-600 to-gray-700"
            : "bg-gray-100 text-gray-600";
      }
    };

    return (
      <div
        onClick={() => isMobile && setSidebarOpen(false)}
        className={`
          group flex items-center px-4 py-4 text-base font-medium rounded-xl transition-all cursor-pointer
          ${isActive ? "bg-gray-50 shadow-sm" : "hover:bg-gray-50"}
          ${!isMobile && sidebarCollapsed ? "justify-center" : ""}
        `}
      >
        <div
          className={`
          p-3 rounded-lg transition-all
          ${
            isActive
              ? `bg-gradient-to-br ${getIconColor()} text-white shadow-md`
              : getIconColor()
          }
        `}
        >
          <item.icon className="h-5 w-5" />
        </div>
        {(!sidebarCollapsed || isMobile) && (
          <>
            <span
              className={`ml-3 flex-1 ${
                isActive ? "text-gray-900 font-semibold" : "text-gray-600"
              }`}
            >
              {item.name}
            </span>
            {item.badge && (
              <span
                className={`
                ml-auto inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                ${
                  isActive
                    ? "bg-gray-200 text-gray-700"
                    : "bg-gray-100 text-gray-600"
                }
              `}
              >
                {item.badge}
              </span>
            )}
          </>
        )}
      </div>
    );
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div
        className={`fixed inset-0 z-50 lg:hidden ${
          sidebarOpen ? "" : "hidden"
        }`}
      >
        <div
          className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
          onClick={() => setSidebarOpen(false)}
        />
        <div className="fixed inset-y-0 left-0 flex w-72 flex-col bg-white shadow-2xl">
          <div className="flex h-20 items-center justify-between px-6 border-b border-gray-200">
            <Logo showText={true} />
            <button
              type="button"
              className="text-gray-600 hover:text-gray-900 p-3 hover:bg-gray-100 rounded-lg transition-colors"
              onClick={() => setSidebarOpen(false)}
            >
              <XCircle className="h-7 w-7" />
            </button>
          </div>

          <nav className="flex-1 space-y-2 px-4 py-6 overflow-y-auto">
            {navigation.map((item) => (
              <NavLink key={item.name} item={item} isMobile />
            ))}
          </nav>
        </div>
      </div>

      <div
        className={`
        hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col bg-white border-r border-gray-200 transition-all duration-300
        ${sidebarCollapsed ? "lg:w-16" : "lg:w-64"}
      `}
      >
        <div className="flex h-20 items-center justify-between px-4 border-b border-gray-200">
          {!sidebarCollapsed && <Logo showText={true} />}
          <button
            onClick={() => setSidebarCollapsed(!sidebarCollapsed)}
            className={`p-3 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all ${
              sidebarCollapsed ? "mx-auto" : ""
            }`}
          >
            {sidebarCollapsed ? (
              <ChevronRight className="h-6 w-6" />
            ) : (
              <ChevronLeft className="h-6 w-6" />
            )}
          </button>
        </div>

        <nav className="flex-1 space-y-2 px-4 py-6 overflow-y-auto">
          {navigation.map((item) => (
            <div key={item.name} className="relative group">
              <NavLink item={item} />
              {sidebarCollapsed && (
                <div className="absolute left-full ml-2 px-3 py-2 bg-gray-900 text-white text-base rounded-md whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                  {item.name}
                  {item.badge && (
                    <span className="ml-2 inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-teal-600 text-white">
                      {item.badge}
                    </span>
                  )}
                </div>
              )}
            </div>
          ))}
        </nav>
      </div>

      <div
        className={`transition-all duration-300 ${
          sidebarCollapsed ? "lg:pl-16" : "lg:pl-64"
        }`}
      >
        <header className="sticky top-0 z-40 bg-white border-b border-gray-200">
          <div className="flex h-20 items-center gap-x-4 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
            <button
              type="button"
              className="lg:hidden text-gray-700 hover:text-gray-900 p-2.5 hover:bg-gray-100 rounded-lg transition-colors"
              onClick={() => setSidebarOpen(true)}
            >
              <Menu className="h-6 w-6" />
            </button>

            <div className="flex-1">
              <h1 className="text-xl font-display font-semibold text-gray-900">
                {pathname === "/dashboard" && "Tableau de bord"}
                {pathname === "/rendez-vous" && "Rendez-vous"}
                {pathname === "/prospects" && "Prospects"}
                {pathname === "/clients" && "Clients"}
                {pathname === "/etudes-scolaires" && "Études scolaires"}
                {pathname === "/devis" && "Devis"}
                {pathname === "/utilisateurs" && "Utilisateurs"}
              </h1>
            </div>

            <div className="hidden md:block">
              <div className="relative">
                <SearchCheck className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                  type="search"
                  placeholder="Rechercher..."
                  className="w-64 pl-11 pr-4 py-3 border border-gray-300 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                />
              </div>
            </div>

            <div className="flex items-center gap-x-2">
              <div className="hidden md:flex items-center space-x-2">
                <button className="p-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                  <PhoneCall className="h-5 w-5" />
                </button>
                <button className="p-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                  <MailOpen className="h-5 w-5" />
                </button>
              </div>

              <button className="relative p-2.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                <BellRing className="h-6 w-6" />
                <span className="absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
              </button>

            </div>
          </div>
        </header>

        <main className="py-8">
          <div className="mx-auto px-4 sm:px-6 lg:px-8">{children}</div>
        </main>
      </div>
    </div>
  );
};
