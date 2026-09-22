import MasterDataPage from '@/components/master-data-page';

export default function Index(props: any) {
    return <MasterDataPage {...props} config={{ title: 'Aktivitas', singular: 'Aktivitas', path: '/admin/aktivitas', description: 'Kelola aktivitas yang dapat dilakukan di Tempat Wisata.' }}/>;
}
