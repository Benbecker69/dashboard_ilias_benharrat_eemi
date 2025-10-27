'use client';

import { AppLayout } from '@/components/layout/AppLayout';
import { Card, CardHeader, CardBody } from '@/components/ui/Card';
import { Button } from '@/components/ui/Button';
import {
  UsersRound,
  CalendarDays,
  FileCheck,
  TrendingUp,
  ActivitySquare,
  Sunrise,
  PhoneCall,
  CircleDollarSign,
  ChevronRight,
  PlusCircle,
} from 'lucide-react';
import { useState, useEffect } from 'react';

export default function DashboardPage() {
  const [currentDate, setCurrentDate] = useState('');

  useEffect(() => {
    setCurrentDate(new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' }));
  }, []);
  const stats = [
    {
      name: 'RDV ce mois',
      value: '12',
      icon: CalendarDays,
      change: '+4.5%',
      changeType: 'positive' as const,
      bgColor: 'bg-blue-500',
      iconColor: 'text-blue-600',
    },
    {
      name: 'Clients actifs',
      value: '8',
      icon: UsersRound,
      change: '+2.1%',
      changeType: 'positive' as const,
      bgColor: 'bg-green-500',
      iconColor: 'text-green-600',
    },
    {
      name: 'Devis en cours',
      value: '5',
      icon: FileCheck,
      change: '-1.2%',
      changeType: 'negative' as const,
      bgColor: 'bg-amber-500',
      iconColor: 'text-amber-600',
    },
    {
      name: 'CA du mois',
      value: '45k€',
      icon: CircleDollarSign,
      change: '+18%',
      changeType: 'positive' as const,
      bgColor: 'bg-teal-500',
      iconColor: 'text-teal-600',
    },
  ];

  const recentActivities = [
    {
      id: 1,
      type: 'rdv',
      title: 'Rendez-vous avec M. Dupont',
      time: 'Il y a 2 heures',
      status: 'completed',
      icon: CalendarDays,
      client: 'Installation 6kWc',
    },
    {
      id: 2,
      type: 'devis',
      title: 'Devis #2024-087 envoyé',
      time: 'Il y a 4 heures',
      status: 'pending',
      icon: FileCheck,
      client: 'Mme Martin - 9kWc',
    },
    {
      id: 3,
      type: 'client',
      title: 'Nouveau client : Mme Rousseau',
      time: 'Hier',
      status: 'new',
      icon: UsersRound,
      client: 'Prospect chaud',
    },
  ];

  const upcomingAppointments = [
    {
      id: 1,
      name: 'M. Bernard',
      time: '14h30',
      type: 'Installation',
      address: '12 rue de la République, Lyon',
      phone: '06 12 34 56 78',
      status: 'urgent' as const
    },
    {
      id: 2,
      name: 'Mme Rousseau',
      time: '16h00',
      type: 'Visite technique',
      address: '45 avenue Jean Jaurès, Villeurbanne',
      phone: '07 98 76 54 32',
      status: 'normal' as const
    },
    {
      id: 3,
      name: 'M. Lefebvre',
      time: '17h30',
      type: 'Signature',
      address: '8 place Bellecour, Lyon',
      phone: '06 45 67 89 10',
      status: 'normal' as const
    },
  ];

  const getActivityBadge = (status: string) => {
    switch (status) {
      case 'completed':
        return <span className="text-sm text-green-600 font-medium">Terminé</span>;
      case 'pending':
        return <span className="text-sm text-amber-600 font-medium">En attente</span>;
      case 'new':
        return <span className="text-sm text-blue-600 font-medium">Nouveau</span>;
      default:
        return null;
    }
  };

  return (
    <AppLayout>
      <div className="space-y-8">
        <div className="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-7 border border-indigo-200">
          <div className="flex items-center justify-between mb-5">
            <h2 className="text-lg font-display font-semibold text-gray-900 flex items-center">
              <CalendarDays className="h-6 w-6 mr-3 text-indigo-600" />
              Rendez-vous du jour
            </h2>
            <span className="text-base text-gray-600 bg-white px-4 py-2 rounded-full">
              {currentDate}
            </span>
          </div>

          {upcomingAppointments.length > 0 ? (
            <div className="space-y-4">
              {upcomingAppointments.map((appointment) => (
                <div
                  key={appointment.id}
                  className="flex items-center justify-between p-5 bg-white rounded-lg hover:shadow-md transition-all cursor-pointer border border-indigo-100"
                >
                  <div className="flex items-center space-x-4">
                    <div className={`text-xl font-bold ${appointment.status === 'urgent' ? 'text-red-600' : 'text-indigo-700'}`}>
                      {appointment.time}
                    </div>
                    <div className="h-10 w-px bg-gray-300"></div>
                    <div>
                      <div className="font-display font-medium text-gray-900 text-base">{appointment.name}</div>
                      <div className="text-sm text-gray-500 flex items-center mt-1">
                        <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium mr-2 ${
                          appointment.type === 'Installation' ? 'bg-green-100 text-green-700' :
                          appointment.type === 'Visite technique' ? 'bg-blue-100 text-blue-700' :
                          'bg-purple-100 text-purple-700'
                        }`}>
                          {appointment.type}
                        </span>
                        • {appointment.address.split(',')[1]}
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center space-x-3">
                    <a
                      href={`tel:${appointment.phone}`}
                      className="p-3 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                    >
                      <PhoneCall className="h-5 w-5" />
                    </a>
                    <ChevronRight className="h-5 w-5 text-gray-400" />
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-10 bg-white rounded-lg">
              <CalendarDays className="h-14 w-14 text-indigo-200 mx-auto mb-3" />
              <p className="text-base text-gray-500">Aucun rendez-vous aujourd&apos;hui</p>
            </div>
          )}
        </div>

        <div className="grid grid-cols-2 lg:grid-cols-4 gap-5">
          <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
            <div className="flex items-center justify-between mb-4">
              <CalendarDays className="h-8 w-8 text-blue-200" />
              <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                stats[0].changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
              }`}>
                {stats[0].change}
              </span>
            </div>
            <div>
              <p className="text-3xl font-display font-bold">{stats[0].value}</p>
              <p className="text-sm text-blue-100 mt-1">{stats[0].name}</p>
            </div>
          </div>

          <div className="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
            <div className="flex items-center justify-between mb-4">
              <UsersRound className="h-8 w-8 text-green-200" />
              <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                stats[1].changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
              }`}>
                {stats[1].change}
              </span>
            </div>
            <div>
              <p className="text-3xl font-display font-bold">{stats[1].value}</p>
              <p className="text-sm text-green-100 mt-1">{stats[1].name}</p>
            </div>
          </div>

          <div className="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
            <div className="flex items-center justify-between mb-4">
              <FileCheck className="h-8 w-8 text-amber-200" />
              <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                stats[2].changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
              }`}>
                {stats[2].change}
              </span>
            </div>
            <div>
              <p className="text-3xl font-display font-bold">{stats[2].value}</p>
              <p className="text-sm text-amber-100 mt-1">{stats[2].name}</p>
            </div>
          </div>

          <div className="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-7 text-white shadow-lg hover:shadow-xl transition-all hover:scale-105">
            <div className="flex items-center justify-between mb-4">
              <CircleDollarSign className="h-8 w-8 text-purple-200" />
              <span className={`text-sm font-bold px-3 py-1 rounded-full ${
                stats[3].changeType === 'positive' ? 'bg-green-400 text-green-900' : 'bg-red-400 text-red-900'
              }`}>
                {stats[3].change}
              </span>
            </div>
            <div>
              <p className="text-3xl font-display font-bold">{stats[3].value}</p>
              <p className="text-sm text-purple-100 mt-1">{stats[3].name}</p>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <Card className="bg-white rounded-lg border border-gray-200">
            <CardHeader
              title="Activité récente"
              icon={ActivitySquare}
              action={
                <Button
                  variant="ghost"
                  size="sm"
                  className="text-sm text-gray-600 hover:text-gray-900"
                >
                  Voir tout
                </Button>
              }
            />
            <CardBody className="p-6">
              <div className="space-y-5">
                {recentActivities.map((activity) => (
                  <div key={activity.id} className="flex items-center justify-between py-2">
                    <div className="flex items-center space-x-4">
                      <activity.icon className={`h-5 w-5 text-gray-400`} />
                      <div>
                        <p className="text-base font-display font-medium text-gray-900">{activity.title}</p>
                        <p className="text-sm text-gray-500 mt-1">{activity.time}</p>
                      </div>
                    </div>
                    {getActivityBadge(activity.status)}
                  </div>
                ))}
              </div>
            </CardBody>
          </Card>

          <Card className="bg-white rounded-xl shadow-sm border border-gray-200">
            <CardHeader
              title={
                <span className="flex items-center">
                  <TrendingUp className="h-6 w-6 mr-2 text-green-600" />
                  Performance
                </span>
              }
            />
            <CardBody className="p-6">
              <div className="space-y-5">
                <div className="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg">
                  <div className="flex items-center space-x-3">
                    <div className="h-2.5 w-2.5 bg-blue-500 rounded-full animate-pulse"></div>
                    <span className="text-base text-gray-700">Visites effectuées</span>
                  </div>
                  <div className="flex items-center space-x-3">
                    <span className="text-base font-bold text-gray-900">18/25</span>
                    <div className="w-20 bg-gray-200 rounded-full h-2.5">
                      <div className="bg-gradient-to-r from-blue-500 to-indigo-600 h-2.5 rounded-full" style={{width: '72%'}}></div>
                    </div>
                  </div>
                </div>
                <div className="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                  <div className="flex items-center space-x-3">
                    <div className="h-2.5 w-2.5 bg-green-500 rounded-full animate-pulse"></div>
                    <span className="text-base text-gray-700">Devis signés</span>
                  </div>
                  <div className="flex items-center space-x-3">
                    <span className="text-base font-bold text-gray-900">5/8</span>
                    <div className="w-20 bg-gray-200 rounded-full h-2.5">
                      <div className="bg-gradient-to-r from-green-500 to-emerald-600 h-2.5 rounded-full" style={{width: '62.5%'}}></div>
                    </div>
                  </div>
                </div>
                <div className="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                  <div className="flex items-center space-x-3">
                    <div className="h-2.5 w-2.5 bg-purple-500 rounded-full animate-pulse"></div>
                    <span className="text-base text-gray-700">Taux de conversion</span>
                  </div>
                  <span className="text-base font-bold text-purple-600">62.5%</span>
                </div>
                <div className="pt-4 border-t">
                  <div className="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg">
                    <span className="text-base font-medium text-gray-900 flex items-center">
                      <CircleDollarSign className="h-5 w-5 mr-2 text-amber-600" />
                      Commission estimée
                    </span>
                    <span className="text-xl font-display font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">
                      2 850€
                    </span>
                  </div>
                </div>
              </div>
            </CardBody>
          </Card>
        </div>

        <div className="bg-gradient-to-r from-teal-50 via-blue-50 to-purple-50 rounded-xl p-7 border border-teal-200">
          <h3 className="text-base font-display font-semibold text-gray-900 mb-5 flex items-center">
            <PlusCircle className="h-5 w-5 mr-2 text-teal-600" />
            Actions rapides
          </h3>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <button className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-blue-300 hover:shadow-md transition-all group">
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg group-hover:scale-110 transition-transform">
                  <CalendarDays className="h-6 w-6 text-blue-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Nouveau RDV</span>
              </div>
            </button>
            <button className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-green-300 hover:shadow-md transition-all group">
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg group-hover:scale-110 transition-transform">
                  <UsersRound className="h-6 w-6 text-green-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Nouveau client</span>
              </div>
            </button>
            <button className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-amber-300 hover:shadow-md transition-all group">
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-amber-100 to-orange-100 rounded-lg group-hover:scale-110 transition-transform">
                  <FileCheck className="h-6 w-6 text-amber-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Créer devis</span>
              </div>
            </button>
            <button className="p-4 bg-white rounded-lg border-2 border-transparent hover:border-purple-300 hover:shadow-md transition-all group">
              <div className="flex flex-col items-center space-y-2">
                <div className="p-3 bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg group-hover:scale-110 transition-transform">
                  <Sunrise className="h-6 w-6 text-purple-600" />
                </div>
                <span className="text-sm font-medium text-gray-700">Étude solaire</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
