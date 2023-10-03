import { render, screen } from '@testing-library/react';
import Home  from '../book_review/Home';

describe("テスト", () => {
    test('見出しがあるか', () => {
        render(<Home />);
        const hometitle = screen.getByText(/書籍レビュー/);
        expect(hometitle).toBeInTheDocument();
    })
})
