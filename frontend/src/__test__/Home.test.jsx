import { render, screen } from '@testing-library/react';
import Home  from '../book_review/Home';

describe("ホーム画面の要素があるか", () => {
    beforeEach(() => { //「render」は各テスト毎に実行する必要があるから「beforEach」を使う
        render(
          <Provider store={store}>
            <CookiesProvider>
              <BrowserRouter>
                <Home />
              </BrowserRouter>      
            </CookiesProvider>
          </Provider>
          );  
      });

    test('見出しがあるか', () => {
        const hometitle = screen.getByText(/書籍レビュー/);
        expect(hometitle).toBeInTheDocument();
    });

    test('')
})
