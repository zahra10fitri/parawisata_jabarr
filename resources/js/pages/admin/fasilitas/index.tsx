import MasterDataPage from '@/components/master-data-page';

export default function Index(props: any) {
    return <MasterDataPage {...props} config={{ title: 'Fasilitas', singular: 'Fasilitas', path: '/admin/fasilitas', description: 'Kelola fasilitas yang tersedia di Tempat Wisata.' }}/>;
}
