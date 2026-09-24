import MasterDataPage from '@/components/master-data-page';

export default function Index(props: any) {
    return <MasterDataPage {...props} config={{ title: 'Pengalaman', singular: 'Pengalaman', path: '/admin/pengalaman', description: 'Kelola kategori pengalaman yang digunakan pada Tempat Wisata.', hasDetails: true, hasImageUpload: true }}/>;
}
